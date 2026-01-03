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

use chillerlan\QRCode\QRCode;

use function mb_strlen, ord, sprintf, strlen;

class Kanji extends QRDataAbstract{

	protected $datamode = QRCode::DATA_KANJI;

	protected $lengthBits = [8, 10, 12];

	protected function getLength(string $data):int{
		return mb_strlen($data, 'SJIS');
	}

	protected function write(string $data):void{
		$len = strlen($data);

		for($i = 0; $i + 1 < $len; $i += 2){
			$c = ((0xff & ord($data[$i])) << 8) | (0xff & ord($data[$i + 1]));

			if(0x8140 <= $c && $c <= 0x9FFC){
				$c -= 0x8140;
			}
			elseif(0xE040 <= $c && $c <= 0xEBBF){
				$c -= 0xC140;
			}
			else{
				throw new QRCodeDataException(sprintf('illegal char at %d [%d]', $i + 1, $c));
			}

			$this->bitBuffer->put((($c >> 8) & 0xff) * 0xC0 + ($c & 0xff), 13);

		}

		if($i < $len){
			throw new QRCodeDataException(sprintf('illegal char at %d', $i + 1));
		}

	}

}

