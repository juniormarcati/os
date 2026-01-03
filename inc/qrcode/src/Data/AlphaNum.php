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

use function array_search, ord, sprintf;

class AlphaNum extends QRDataAbstract{

	protected $datamode = QRCode::DATA_ALPHANUM;

	protected $lengthBits = [9, 11, 13];

	protected function write(string $data):void{

		for($i = 0; $i + 1 < $this->strlen; $i += 2){
			$this->bitBuffer->put($this->getCharCode($data[$i]) * 45 + $this->getCharCode($data[$i + 1]), 11);
		}

		if($i < $this->strlen){
			$this->bitBuffer->put($this->getCharCode($data[$i]), 6);
		}

	}

	protected function getCharCode(string $chr):int{
		$i = array_search($chr, $this::ALPHANUM_CHAR_MAP);

		if($i !== false){
			return $i;
		}

		throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $chr, ord($chr)));
	}

}

