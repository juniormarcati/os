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

use FPDF;
use chillerlan\QRCode\Output\{QRFpdf, QROutputInterface};
use chillerlan\QRCode\{QRCode, QROptions};

use function class_exists, substr;

class QRFpdfTest extends QROutputTestAbstract{

	protected $FQCN = QRFpdf::class;

	public function setUp():void{

		if(!class_exists(FPDF::class)){
			$this->markTestSkipped('FPDF not available');
			return;
		}

		parent::setUp();
	}

	public function testSetModuleValues():void{

		$this->options->moduleValues = [
			1024 => [0, 0, 0],
			4    => [255, 255, 255],
		];

		$this->outputInterface->dump();

		$this::assertTrue(true); 
	}

	public function testRenderImage():void{
		$type = QRCode::OUTPUT_FPDF;

		$this->options->outputType  = $type;
		$this->options->imageBase64 = false;
		$this->outputInterface->dump($this::cachefile.$type);

		$expected = substr(file_get_contents($this::cachefile.$type), 0, 2000);
		$actual   = substr($this->outputInterface->dump(), 0, 2000);

		$this::assertSame($expected, $actual);
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;

		$this->setOutputInterface();

		$this::assertInstanceOf(FPDF::class, $this->outputInterface->dump());
	}

}

