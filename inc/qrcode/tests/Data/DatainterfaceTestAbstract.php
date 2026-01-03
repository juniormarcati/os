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



namespace chillerlan\QRCodeTest\Data;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\{QRCodeDataException, QRDataInterface, QRMatrix};
use chillerlan\QRCodeTest\QRTestAbstract;

abstract class DatainterfaceTestAbstract extends QRTestAbstract{

	protected $dataInterface;

	protected $testdata;
	protected $expected;

	protected function setUp():void{
		parent::setUp();

		$this->dataInterface = $this->reflection->newInstanceArgs([new QROptions(['version' => 4])]);
	}

	public function testInstance(){
		$this->dataInterface = $this->reflection->newInstanceArgs([new QROptions, $this->testdata]);

		$this->assertInstanceOf(QRDataInterface::class, $this->dataInterface);
	}

	public function testSetData(){
		$this->dataInterface->setData($this->testdata);

		$this->assertSame($this->expected, $this->getProperty('matrixdata')->getValue($this->dataInterface));
	}

	public function testInitMatrix(){
		$m = $this->dataInterface->setData($this->testdata)->initMatrix(0);

		$this->assertInstanceOf(QRMatrix::class, $m);
	}

	public function testGetMinimumVersion(){
		$this->assertSame(1, $this->getMethod('getMinimumVersion')->invoke($this->dataInterface));
	}

	public function testGetMinimumVersionException(){
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('data exceeds');

		$this->getProperty('strlen')->setValue($this->dataInterface, 13370);
		$this->getMethod('getMinimumVersion')->invoke($this->dataInterface);
	}

}

