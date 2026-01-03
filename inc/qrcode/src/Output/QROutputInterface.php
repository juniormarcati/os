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

interface QROutputInterface{

	const DEFAULT_MODULE_VALUES = [
		QRMatrix::M_DATA            => false, 
		QRMatrix::M_FINDER          => false, 
		QRMatrix::M_SEPARATOR       => false, 
		QRMatrix::M_ALIGNMENT       => false, 
		QRMatrix::M_TIMING          => false, 
		QRMatrix::M_FORMAT          => false, 
		QRMatrix::M_VERSION         => false, 
		QRMatrix::M_QUIETZONE       => false, 
		QRMatrix::M_LOGO            => false, 
		QRMatrix::M_TEST            => false, 
		QRMatrix::M_DARKMODULE << 8 => true,  
		QRMatrix::M_DATA << 8       => true,  
		QRMatrix::M_FINDER << 8     => true,  
		QRMatrix::M_ALIGNMENT << 8  => true,  
		QRMatrix::M_TIMING << 8     => true,  
		QRMatrix::M_FORMAT << 8     => true,  
		QRMatrix::M_VERSION << 8    => true,  
		QRMatrix::M_FINDER_DOT << 8 => true,  
		QRMatrix::M_TEST << 8       => true,  
	];

	public function dump(string $file = null);

}

