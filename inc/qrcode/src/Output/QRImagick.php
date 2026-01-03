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



namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCodeException;
use chillerlan\Settings\SettingsContainerInterface;
use Imagick, ImagickDraw, ImagickPixel;

use function is_string;

class QRImagick extends QROutputAbstract{

	protected $imagick;

	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!extension_loaded('imagick')){
			throw new QRCodeException('ext-imagick not loaded'); 
		}

		parent::__construct($options, $matrix);
	}

	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $type => $defaultValue){
			$v = $this->options->moduleValues[$type] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$type] = $defaultValue
					? new ImagickPixel($this->options->markupDark)
					: new ImagickPixel($this->options->markupLight);
			}
			else{
				$this->moduleValues[$type] = new ImagickPixel($v);
			}
		}
	}

	public function dump(string $file = null){
		$file          = $file ?? $this->options->cachefile;
		$this->imagick = new Imagick;

		$this->imagick->newImage(
			$this->length,
			$this->length,
			new ImagickPixel($this->options->imagickBG ?? 'transparent'),
			$this->options->imagickFormat
		);

		$this->drawImage();

		if($this->options->returnResource){
			return $this->imagick;
		}

		$imageData = $this->imagick->getImageBlob();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		return $imageData;
	}

	protected function drawImage():void{
		$draw = new ImagickDraw;

		foreach($this->matrix->matrix() as $y => $row){
			foreach($row as $x => $M_TYPE){
				$draw->setStrokeColor($this->moduleValues[$M_TYPE]);
				$draw->setFillColor($this->moduleValues[$M_TYPE]);
				$draw->rectangle(
					$x * $this->scale,
					$y * $this->scale,
					($x + 1) * $this->scale,
					($y + 1) * $this->scale
				);

			}
		}

		$this->imagick->drawImage($draw);
	}

}

