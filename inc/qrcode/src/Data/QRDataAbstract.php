<?php

/**
 * ------------------------------------------------------------------------
 * Plugin OS – Community Edition
 * Copyright (C) 2016-2026 Marcati
 * https://github.com/juniormarcati
 * ------------------------------------------------------------------------
 * This file is part of Plugin OS.
 *
 * Plugin OS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Plugin OS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Plugin OS. If not, see <https://www.gnu.org/licenses/>.
 * ------------------------------------------------------------------------
 *
 * @package   PluginOS
 * @author    Marcati
 * @copyright 2016-2026 Marcati
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/juniormarcati/os
 * @since     2016
 * ------------------------------------------------------------------------
 */



namespace chillerlan\QRCode\Data;

use chillerlan\QRCode\{QRCode, QRCodeException};
use chillerlan\QRCode\Helpers\{BitBuffer, Polynomial};
use chillerlan\Settings\SettingsContainerInterface;

use function array_fill, array_merge, count, max, mb_convert_encoding, mb_detect_encoding, range, sprintf, strlen;

abstract class QRDataAbstract implements QRDataInterface{

	protected $strlen;

	protected $datamode;

	protected $lengthBits = [0, 0, 0];

	protected $version;

	protected $matrixdata;

	protected $ecdata;

	protected $dcdata;

	protected $options;

	protected $bitBuffer;

	public function __construct(SettingsContainerInterface $options, string $data = null){
		$this->options = $options;

		if($data !== null){
			$this->setData($data);
		}
	}

	public function setData(string $data):QRDataInterface{

		if($this->datamode === QRCode::DATA_KANJI){
			$data = mb_convert_encoding($data, 'SJIS', mb_detect_encoding($data));
		}

		$this->strlen  = $this->getLength($data);
		$this->version = $this->options->version === QRCode::VERSION_AUTO
			? $this->getMinimumVersion()
			: $this->options->version;

		$this->matrixdata = $this
			->writeBitBuffer($data)
			->maskECC()
		;

		return $this;
	}

	public function initMatrix(int $maskPattern, bool $test = null):QRMatrix{
		return (new QRMatrix($this->version, $this->options->eccLevel))
			->setFinderPattern()
			->setSeparators()
			->setAlignmentPattern()
			->setTimingPattern()
			->setVersionNumber($test)
			->setFormatInfo($maskPattern, $test)
			->setDarkModule()
			->mapData($this->matrixdata, $maskPattern)
		;
	}

	protected function getLengthBits():int{

		 foreach([9, 26, 40] as $key => $breakpoint){
			 if($this->version <= $breakpoint){
				 return $this->lengthBits[$key];
			 }
		 }

		throw new QRCodeDataException(sprintf('invalid version number: %d', $this->version));
	}

	protected function getLength(string $data):int{
		return strlen($data);
	}

	protected function getMinimumVersion():int{
		$maxlength = 0;

		foreach(range($this->options->versionMin, $this->options->versionMax) as $version){
			$maxlength = $this::MAX_LENGTH[$version][QRCode::DATA_MODES[$this->datamode]][QRCode::ECC_MODES[$this->options->eccLevel]];

			if($this->strlen <= $maxlength){
				return $version;
			}
		}

		throw new QRCodeDataException(sprintf('data exceeds %d characters', $maxlength));
	}

	abstract protected function write(string $data):void;

	protected function writeBitBuffer(string $data):QRDataInterface{
		$this->bitBuffer = new BitBuffer;

		$MAX_BITS = $this::MAX_BITS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$this->bitBuffer
			->clear()
			->put($this->datamode, 4)
			->put($this->strlen, $this->getLengthBits())
		;

		$this->write($data);

		if($this->bitBuffer->length > $MAX_BITS){
			throw new QRCodeException(sprintf('code length overflow. (%d > %d bit)', $this->bitBuffer->length, $MAX_BITS)); 
		}

		if($this->bitBuffer->length + 4 <= $MAX_BITS){
			$this->bitBuffer->put(0, 4);
		}

		while($this->bitBuffer->length % 8 !== 0){
			$this->bitBuffer->putBit(false);
		}

		while(true){

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0xEC, 8);

			if($this->bitBuffer->length >= $MAX_BITS){
				break;
			}

			$this->bitBuffer->put(0x11, 8);
		}

		return $this;
	}

	protected function maskECC():array{
		[$l1, $l2, $b1, $b2] = $this::RSBLOCKS[$this->version][QRCode::ECC_MODES[$this->options->eccLevel]];

		$rsBlocks     = array_fill(0, $l1, [$b1, $b2]);
		$rsCount      = $l1 + $l2;
		$this->ecdata = array_fill(0, $rsCount, null);
		$this->dcdata = $this->ecdata;

		if($l2 > 0){
			$rsBlocks = array_merge($rsBlocks, array_fill(0, $l2, [$b1 + 1, $b2 + 1]));
		}

		$totalCodeCount = 0;
		$maxDcCount     = 0;
		$maxEcCount     = 0;
		$offset         = 0;

		foreach($rsBlocks as $key => $block){
			[$rsBlockTotal, $dcCount] = $block;

			$ecCount            = $rsBlockTotal - $dcCount;
			$maxDcCount         = max($maxDcCount, $dcCount);
			$maxEcCount         = max($maxEcCount, $ecCount);
			$this->dcdata[$key] = array_fill(0, $dcCount, null);

			foreach($this->dcdata[$key] as $a => $_z){
				$this->dcdata[$key][$a] = 0xff & $this->bitBuffer->buffer[$a + $offset];
			}

			[$num, $add] = $this->poly($key, $ecCount);

			foreach($this->ecdata[$key] as $c => $_z){
				$modIndex               = $c + $add;
				$this->ecdata[$key][$c] = $modIndex >= 0 ? $num[$modIndex] : 0;
			}

			$offset         += $dcCount;
			$totalCodeCount += $rsBlockTotal;
		}

		$data  = array_fill(0, $totalCodeCount, null);
		$index = 0;

		$mask = function($arr, $count) use (&$data, &$index, $rsCount){
			for($x = 0; $x < $count; $x++){
				for($y = 0; $y < $rsCount; $y++){
					if($x < count($arr[$y])){
						$data[$index] = $arr[$y][$x];
						$index++;
					}
				}
			}
		};

		$mask($this->dcdata, $maxDcCount);
		$mask($this->ecdata, $maxEcCount);

		return $data;
	}

	protected function poly(int $key, int $count):array{
		$rsPoly  = new Polynomial;
		$modPoly = new Polynomial;

		for($i = 0; $i < $count; $i++){
			$modPoly->setNum([1, $modPoly->gexp($i)]);
			$rsPoly->multiply($modPoly->getNum());
		}

		$rsPolyCount = count($rsPoly->getNum());

		$modPoly
			->setNum($this->dcdata[$key], $rsPolyCount - 1)
			->mod($rsPoly->getNum())
		;

		$this->ecdata[$key] = array_fill(0, $rsPolyCount - 1, null);
		$num                = $modPoly->getNum();

		return [
			$num,
			count($num) - count($this->ecdata[$key]),
		];
	}

}

