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

use chillerlan\QRCode\Data\{Kanji, QRCodeDataException};

class KanjiTest extends DatainterfaceTestAbstract{

	protected $FQCN = Kanji::class;
	protected $testdata = '茗荷茗荷茗荷茗荷茗荷';
	protected $expected = [
		128, 173, 85, 26, 95, 85, 70, 151,
		213, 81, 165, 245, 84, 105, 125, 85,
		26, 92, 0, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		17, 236, 17, 236, 17, 236, 17, 236,
		195, 11, 221, 91, 141, 220, 163, 46,
		165, 37, 163, 176, 79, 0, 64, 68,
		96, 113, 54, 191
	];

	public function testIllegalCharException1(){
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1 [16191]');

		$this->dataInterface->setData('ÃÃ');
	}

	public function testIllegalCharException2(){
		$this->expectException(QRCodeDataException::class);
		$this->expectExceptionMessage('illegal char at 1');

		$this->dataInterface->setData('Ã');
	}
}

