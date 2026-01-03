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



namespace chillerlan\QRCodeTest;

use chillerlan\QRCode\{QRCode, QRCodeException, QROptions};
use PHPUnit\Framework\TestCase;

class QROptionsTest extends TestCase{

	protected $options;

	public function testVersionClamp(){
		$this->assertSame(40, (new QROptions(['version' => 42]))->version);
		$this->assertSame(1, (new QROptions(['version' => -42]))->version);
		$this->assertSame(21, (new QROptions(['version' => 21]))->version);
		$this->assertSame(QRCode::VERSION_AUTO, (new QROptions)->version); 
	}

	public function testVersionMinMaxClamp(){
		$o = new QROptions(['versionMin' => 5, 'versionMax' => 10]);
		$this->assertSame(5, $o->versionMin);
		$this->assertSame(10, $o->versionMax);

		$o = new QROptions(['versionMin' => -42, 'versionMax' => 42]);
		$this->assertSame(1, $o->versionMin);
		$this->assertSame(40, $o->versionMax);

		$o = new QROptions(['versionMin' => 10, 'versionMax' => 5]);
		$this->assertSame(5, $o->versionMin);
		$this->assertSame(10, $o->versionMax);

		$o = new QROptions(['versionMin' => 42, 'versionMax' => -42]);
		$this->assertSame(1, $o->versionMin);
		$this->assertSame(40, $o->versionMax);
	}

	public function testMaskPatternClamp(){
		$this->assertSame(7, (new QROptions(['maskPattern' => 42]))->maskPattern);
		$this->assertSame(0, (new QROptions(['maskPattern' => -42]))->maskPattern);
		$this->assertSame(QRCode::MASK_PATTERN_AUTO, (new QROptions)->maskPattern); 
	}

	public function testInvalidEccLevelException(){
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid error correct level: 42');

		new QROptions(['eccLevel' => 42]);
	}

	public function testClampRGBValues(){
		$o = new QROptions(['imageTransparencyBG' => [-1, 0, 999]]);

		$this->assertSame(0, $o->imageTransparencyBG[0]);
		$this->assertSame(0, $o->imageTransparencyBG[1]);
		$this->assertSame(255, $o->imageTransparencyBG[2]);
	}

	public function testInvalidRGBValueException(){
		$this->expectException(QRCodeException::class);
		$this->expectExceptionMessage('Invalid RGB value.');

		new QROptions(['imageTransparencyBG' => ['r', 'g', 'b']]);
	}
}

