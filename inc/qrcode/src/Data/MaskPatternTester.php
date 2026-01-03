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



namespace chillerlan\QRCode\Data;

use function abs, call_user_func_array;

class MaskPatternTester{

	protected $matrix;

	protected $moduleCount;

	public function __construct(QRMatrix $matrix){
		$this->matrix      = $matrix;
		$this->moduleCount = $this->matrix->size();
	}

	public function testPattern():int{
		$penalty  = 0;

		for($level = 1; $level <= 4; $level++){
			$penalty += call_user_func_array([$this, 'testLevel'.$level], [$this->matrix->matrix(true)]);
		}

		return (int)$penalty;
	}

	protected function testLevel1(array $m):int{
		$penalty = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				$count = 0;

				for($ry = -1; $ry <= 1; $ry++){

					if($y + $ry < 0 || $this->moduleCount <= $y + $ry){
						continue;
					}

					for($rx = -1; $rx <= 1; $rx++){

						if(($ry === 0 && $rx === 0) || (($x + $rx) < 0 || $this->moduleCount <= ($x + $rx))){
							continue;
						}

						if($m[$y + $ry][$x + $rx] === $val){
							$count++;
						}

					}
				}

				if($count > 5){
					$penalty += (3 + $count - 5);
				}

			}
		}

		return $penalty;
	}

	protected function testLevel2(array $m):int{
		$penalty = 0;

		foreach($m as $y => $row){

			if($y > ($this->moduleCount - 2)){
				break;
			}

			foreach($row as $x => $val){

				if($x > ($this->moduleCount - 2)){
					break;
				}

				if(
					   $val === $m[$y][$x + 1]
					&& $val === $m[$y + 1][$x]
					&& $val === $m[$y + 1][$x + 1]
				){
					$penalty++;
				}
			}
		}

		return 3 * $penalty;
	}

	protected function testLevel3(array $m):int{
		$penalties = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){

				if(
					($x + 6) < $this->moduleCount
					&&  $val
					&& !$m[$y][$x + 1]
					&&  $m[$y][$x + 2]
					&&  $m[$y][$x + 3]
					&&  $m[$y][$x + 4]
					&& !$m[$y][$x + 5]
					&&  $m[$y][$x + 6]
				){
					$penalties++;
				}

				if(
					($y + 6) < $this->moduleCount
					&&  $val
					&& !$m[$y + 1][$x]
					&&  $m[$y + 2][$x]
					&&  $m[$y + 3][$x]
					&&  $m[$y + 4][$x]
					&& !$m[$y + 5][$x]
					&&  $m[$y + 6][$x]
				){
					$penalties++;
				}

			}
		}

		return $penalties * 40;
	}

	protected function testLevel4(array $m):float{
		$count = 0;

		foreach($m as $y => $row){
			foreach($row as $x => $val){
				if($val){
					$count++;
				}
			}
		}

		return (abs(100 * $count / $this->moduleCount / $this->moduleCount - 50) / 5) * 10;
	}

}

