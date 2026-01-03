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



namespace chillerlan\QRCodeTest\Output;

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Data\Byte;
use chillerlan\QRCode\Output\{QRCodeOutputException, QROutputInterface};
use chillerlan\QRCodeTest\QRTestAbstract;

use function dirname, file_exists, mkdir;

abstract class QROutputTestAbstract extends QRTestAbstract{

	const cachefile = __DIR__.'/../../.build/output_test/test.';

	protected $outputInterface;

	protected $options;

	protected $matrix;

	protected function setUp():void{
		parent::setUp();

		$buildDir = dirname($this::cachefile);
		if(!file_exists($buildDir)){
			mkdir($buildDir, 0777, true);
		}

		$this->options = new QROptions;
		$this->setOutputInterface();
	}

	protected function setOutputInterface(){
		$this->outputInterface = $this->reflection->newInstanceArgs([$this->options, (new Byte($this->options, 'testdata'))->initMatrix(0)]);
		return $this->outputInterface;
	}

	public function testInstance(){
		$this->assertInstanceOf(QROutputInterface::class, $this->outputInterface);
	}

	public function testSaveException(){
		$this->expectException(QRCodeOutputException::class);
		$this->expectExceptionMessage('Could not write data to cache file: /foo');

		$this->options->cachefile = '/foo';
		$this->setOutputInterface();
		$this->outputInterface->dump();
	}

}

