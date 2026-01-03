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

use Imagick;
use chillerlan\QRCode\{QRCode, Output\QRImagick};

class QRImagickTest extends QROutputTestAbstract{

	protected $FQCN = QRImagick::class;

	public function setUp():void{

		if(!extension_loaded('imagick')){
			$this->markTestSkipped('ext-imagick not loaded');
			return;
		}

		parent::setUp();
	}

	public function testImageOutput(){
		$type = QRCode::OUTPUT_IMAGICK;

		$this->options->outputType = $type;
		$this->setOutputInterface();
		$this->outputInterface->dump($this::cachefile.$type);
		$img = $this->outputInterface->dump();

		$this->assertSame($img, file_get_contents($this::cachefile.$type));
	}

	public function testSetModuleValues(){

		$this->options->moduleValues = [
			1024 => '#4A6000',
			4    => '#ECF9BE',
		];

		$this->setOutputInterface()->dump();

		$this->assertTrue(true); 
	}

	public function testOutputGetResource():void{
		$this->options->returnResource = true;

		$this->setOutputInterface();

		$this::assertInstanceOf(Imagick::class, $this->outputInterface->dump());
	}

}

