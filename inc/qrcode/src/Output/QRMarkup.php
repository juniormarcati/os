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

use chillerlan\QRCode\QRCode;

use function is_string, sprintf, strip_tags, trim;

class QRMarkup extends QROutputAbstract{

	protected $defaultMode = QRCode::OUTPUT_MARKUP_SVG;

	protected $svgHeader = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="qr-svg %1$s" style="width: 100%%; height: auto;" viewBox="0 0 %2$d %2$d">';

	protected function setModuleValues():void{

		foreach($this::DEFAULT_MODULE_VALUES as $M_TYPE => $defaultValue){
			$v = $this->options->moduleValues[$M_TYPE] ?? null;

			if(!is_string($v)){
				$this->moduleValues[$M_TYPE] = $defaultValue
					? $this->options->markupDark
					: $this->options->markupLight;
			}
			else{
				$this->moduleValues[$M_TYPE] = trim(strip_tags($v), '\'"');
			}

		}

	}

	protected function html():string{
		$html = '<div class="'.$this->options->cssClass.'">'.$this->options->eol;

		foreach($this->matrix->matrix() as $row){
			$html .= '<div>';

			foreach($row as $M_TYPE){
				$html .= '<span style="background: '.$this->moduleValues[$M_TYPE].';"></span>';
			}

			$html .= '</div>'.$this->options->eol;
		}

		$html .= '</div>'.$this->options->eol;

		if($this->options->cachefile){
			return '<!DOCTYPE html><head><meta charset="UTF-8"></head><body>'.$this->options->eol.$html.'</body>';
		}

		return $html;
	}

	protected function svg():string{
		$matrix = $this->matrix->matrix();

		$svg = sprintf($this->svgHeader, $this->options->cssClass, $this->options->svgViewBoxSize ?? $this->moduleCount)
		       .$this->options->eol
		       .'<defs>'.$this->options->svgDefs.'</defs>'
		       .$this->options->eol;

		foreach($this->moduleValues as $M_TYPE => $value){
			$path = '';

			foreach($matrix as $y => $row){
				$start = null;
				$count = 0;

				foreach($row as $x => $module){

					if($module === $M_TYPE){
						$count++;

						if($start === null){
							$start = $x;
						}

						if(isset($row[$x + 1])){
							continue;
						}
					}

					if($count > 0){
						$len = $count;
						$path .= sprintf('M%s %s h%s v1 h-%sZ ', $start, $y, $len, $len);

						$count = 0;
						$start = null;
					}

				}

			}

			if(!empty($path)){
				$svg .= sprintf('<path class="qr-%s %s" stroke="transparent" fill="%s" fill-opacity="%s" d="%s" />', $M_TYPE, $this->options->cssClass, $value, $this->options->svgOpacity, $path);
			}

		}

		$svg .= '</svg>'.$this->options->eol;

		if($this->options->cachefile){
			return '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.$this->options->eol.$svg;
		}

		if($this->options->imageBase64){
			$svg = sprintf('data:image/svg+xml;base64,%s', base64_encode($svg));
		}

		return $svg;
	}

}

