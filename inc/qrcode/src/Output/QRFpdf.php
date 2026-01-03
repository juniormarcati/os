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



namespace chillerlan\QRCode\Output;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCodeException;
use chillerlan\Settings\SettingsContainerInterface;
use FPDF;

use function array_values, class_exists, count, is_array;

class QRFpdf extends QROutputAbstract{

	public function __construct(SettingsContainerInterface $options, QRMatrix $matrix){

		if(!class_exists(FPDF::class)){
			throw new QRCodeException(
				'The QRFpdf output requires FPDF as dependency but the class "\FPDF" couldn\'t be found.'
			);
		}

		parent::__construct($options, $matrix);
	}

	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!is_array($v) || count($v) < 3){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? [0, 0, 0]
					: [255, 255, 255];
			}
			else{
				$this->moduleValues[$M_TYPE] = array_values($v);
			}

		}

	}

	public function dump(string $file = null){
		$file = $file ?? $this->options->cachefile;

		$fpdf = new FPDF('P', $this->options->fpdfMeasureUnit, [$this->length, $this->length]);
		$fpdf->AddPage();

		$prevColor = null;

		foreach($this->matrix->matrix() as $y => $row){

			foreach($row as $x => $M_TYPE){
				
				$color = $this->moduleValues[$M_TYPE];

				if($prevColor === null || $prevColor !== $color){
					$fpdf->SetFillColor(...$color);
					$prevColor = $color;
				}

				$fpdf->Rect($x * $this->scale, $y * $this->scale, 1 * $this->scale, 1 * $this->scale, 'F');
			}

		}

		if($this->options->returnResource){
			return $fpdf;
		}

		$pdfData = $fpdf->Output('S');

		if($file !== null){
			$this->saveToFile($pdfData, $file);
		}

		if($this->options->imageBase64){
			$pdfData = sprintf('data:application/pdf;base64,%s', base64_encode($pdfData));
		}

		return $pdfData;
	}

}

