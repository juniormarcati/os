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

use chillerlan\QRCode\Helpers\Polynomial;
use chillerlan\QRCode\QRCodeException;
use chillerlan\QRCodeTest\QRTestAbstract;

class PolynomialTest extends QRTestAbstract{

	protected $polynomial;

	protected function setUp():void{
		$this->polynomial = new Polynomial;
	}

	public function testGexp(){
		$this->assertSame(142, $this->polynomial->gexp(-1));
		$this->assertSame(133, $this->polynomial->gexp(128));
		$this->assertSame(2,   $this->polynomial->gexp(256));
	}

	public function testGlogException(){
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('log(0)');

		$this->polynomial->glog(0);
	}
}

