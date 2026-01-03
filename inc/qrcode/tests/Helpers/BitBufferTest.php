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



namespace chillerlan\QRCodeTest\Helpers;

use chillerlan\QRCode\{QRCode, Helpers\BitBuffer};
use chillerlan\QRCodeTest\QRTestAbstract;

class BitBufferTest extends QRTestAbstract{

	protected $bitBuffer;

	protected function setUp():void{
		$this->bitBuffer = new BitBuffer;
	}

	public function bitProvider(){
		return [
			'number'   => [QRCode::DATA_NUMBER, 16],
			'alphanum' => [QRCode::DATA_ALPHANUM, 32],
			'byte'     => [QRCode::DATA_BYTE, 64],
			'kanji'    => [QRCode::DATA_KANJI, 128],
		];
	}

	public function testPut($data, $value){
		$this->bitBuffer->put($data, 4);
		$this->assertSame($value, $this->bitBuffer->buffer[0]);
		$this->assertSame(4, $this->bitBuffer->length);
	}

	public function testClear(){
		$this->bitBuffer->clear();
		$this->assertSame([], $this->bitBuffer->buffer);
		$this->assertSame(0, $this->bitBuffer->length);
	}

}

