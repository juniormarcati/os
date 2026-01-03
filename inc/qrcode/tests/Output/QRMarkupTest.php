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

use chillerlan\QRCode\{QRCode, Output\QRMarkup};

class QRMarkupTest extends QROutputTestAbstract{

	protected $FQCN = QRMarkup::class;

	public function types(){
		return [
			'html' => [QRCode::OUTPUT_MARKUP_HTML],
			'svg'  => [QRCode::OUTPUT_MARKUP_SVG],
		];
	}

	public function testMarkupOutputFile($type){
		$this->options->outputType = $type;
		$this->options->cachefile  = $this::cachefile.$type;
		$this->setOutputInterface();
		$data = $this->outputInterface->dump();

		$this->assertSame($data, file_get_contents($this->options->cachefile));
	}

	public function testMarkupOutput($type){
		$this->options->imageBase64 = false;
		$this->options->outputType  = $type;
		$this->setOutputInterface();

		$expected = explode($this->options->eol, file_get_contents($this::cachefile.$type));
		array_shift($expected);

		if($type === QRCode::OUTPUT_MARKUP_HTML){
			array_pop($expected);
		}

		$expected = implode($this->options->eol, $expected);

		$this->assertSame(trim($expected), trim($this->outputInterface->dump()));
	}

	public function testSetModuleValues(){

		$this->options->imageBase64  = false;
		$this->options->moduleValues = [
			1024 => '#4A6000',
			4    => '#ECF9BE',
		];

		$this->setOutputInterface();
		$data = $this->outputInterface->dump();
		$this->assertStringContainsString('#4A6000', $data);
		$this->assertStringContainsString('#ECF9BE', $data);
	}

}

