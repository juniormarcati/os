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

use function ord, sprintf, substr;

class Number extends QRDataAbstract{

	protected $datamode = QRCode::DATA_NUMBER;

	protected $lengthBits = [10, 12, 14];

	protected function write(string $data):void{
		$i = 0;

		while($i + 2 < $this->strlen){
			$this->bitBuffer->put($this->parseInt(substr($data, $i, 3)), 10);
			$i += 3;
		}

		if($i < $this->strlen){

			if($this->strlen - $i === 1){
				$this->bitBuffer->put($this->parseInt(substr($data, $i, $i + 1)), 4);
			}
			elseif($this->strlen - $i === 2){
				$this->bitBuffer->put($this->parseInt(substr($data, $i, $i + 2)), 7);
			}

		}

	}

	protected function parseInt(string $string):int{
		$num = 0;

		$len = strlen($string);
		for($i = 0; $i < $len; $i++){
			$c = ord($string[$i]);

			if(!in_array($string[$i], $this::NUMBER_CHAR_MAP, true)){
				throw new QRCodeDataException(sprintf('illegal char: "%s" [%d]', $string[$i], $c));
			}

			$c   = $c - 48; 
			$num = $num * 10 + $c;
		}

		return $num;
	}

}

