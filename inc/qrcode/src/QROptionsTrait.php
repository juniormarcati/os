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



namespace chillerlan\QRCode;

use function array_values, count, in_array, is_array, is_numeric, max, min, sprintf, strtolower;

trait QROptionsTrait{

	protected $version = QRCode::VERSION_AUTO;

	protected $versionMin = 1;

	protected $versionMax = 40;

	protected $eccLevel = QRCode::ECC_L;

	protected $maskPattern = QRCode::MASK_PATTERN_AUTO;

	protected $addQuietzone = true;

	protected $quietzoneSize = 4;

	protected $dataMode = null;

	protected $outputType = QRCode::OUTPUT_IMAGE_PNG;

	protected $outputInterface = null;

	protected $cachefile = null;

	protected $eol = PHP_EOL;

	protected $scale = 5;

	protected $cssClass = '';

	protected $svgOpacity = 1.0;

	protected $svgDefs = '<style>rect{shape-rendering:crispEdges}</style>';

	protected $svgViewBoxSize = null;

	protected $textDark = '🔴';

	protected $textLight = '⭕';

	protected $markupDark = '#000';

	protected $markupLight = '#fff';

	protected $returnResource = false;

	protected $imageBase64 = true;

	protected $imageTransparent = true;

	protected $imageTransparencyBG = [255, 255, 255];

	protected $pngCompression = -1;

	protected $jpegQuality = 85;

	protected $imagickFormat = 'png';

	protected $imagickBG = null;

	protected $fpdfMeasureUnit = 'pt';

	protected $moduleValues = null;

	protected function setMinMaxVersion(int $versionMin, int $versionMax):void{
		$min = max(1, min(40, $versionMin));
		$max = max(1, min(40, $versionMax));

		$this->versionMin = min($min, $max);
		$this->versionMax = max($min, $max);
	}

	protected function set_versionMin(int $version):void{
		$this->setMinMaxVersion($version, $this->versionMax);
	}

	protected function set_versionMax(int $version):void{
		$this->setMinMaxVersion($this->versionMin, $version);
	}

	protected function set_eccLevel(int $eccLevel):void{

		if(!isset(QRCode::ECC_MODES[$eccLevel])){
			throw new QRCodeException(sprintf('Invalid error correct level: %s', $eccLevel));
		}

		$this->eccLevel = $eccLevel;
	}

	protected function set_maskPattern(int $maskPattern):void{

		if($maskPattern !== QRCode::MASK_PATTERN_AUTO){
			$this->maskPattern = max(0, min(7, $maskPattern));
		}

	}

	protected function set_imageTransparencyBG($imageTransparencyBG):void{

		if(!is_array($imageTransparencyBG) || count($imageTransparencyBG) < 3){
			$this->imageTransparencyBG = [255, 255, 255];

			return;
		}

		foreach($imageTransparencyBG as $k => $v){

			if(!is_numeric($v)){
				throw new QRCodeException('Invalid RGB value.');
			}

			$this->imageTransparencyBG[$k] = max(0, min(255, (int)$v));
		}

		$this->imageTransparencyBG = array_values($this->imageTransparencyBG);
	}

	protected function set_version(int $version):void{

		if($version !== QRCode::VERSION_AUTO){
			$this->version = max(1, min(40, $version));
		}

	}

	protected function set_fpdfMeasureUnit(string $unit):void{
		$unit = strtolower($unit);

		if(in_array($unit, ['cm', 'in', 'mm', 'pt'], true)){
			$this->fpdfMeasureUnit = $unit;
		}

	}

}

