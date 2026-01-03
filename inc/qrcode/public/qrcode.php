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



namespace chillerlan\QRCodePublic;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

require_once '../vendor/autoload.php';

try{

	$moduleValues = [
		1536 => $_POST['m_finder_dark'],
		6    => $_POST['m_finder_light'],
		2560 => $_POST['m_alignment_dark'],
		10   => $_POST['m_alignment_light'],
		3072 => $_POST['m_timing_dark'],
		12   => $_POST['m_timing_light'],
		3584 => $_POST['m_format_dark'],
		14   => $_POST['m_format_light'],
		4096 => $_POST['m_version_dark'],
		16   => $_POST['m_version_light'],
		1024 => $_POST['m_data_dark'],
		4    => $_POST['m_data_light'],
		512  => $_POST['m_darkmodule_dark'],
		8    => $_POST['m_separator_light'],
		18   => $_POST['m_quietzone_light'],
	];

	$moduleValues = array_map(function($v){
		if(preg_match('/[a-f\d]{6}/i', $v) === 1){
			return in_array($_POST['output_type'], ['png', 'jpg', 'gif'])
				? array_map('hexdec', str_split($v, 2))
				: '#'.$v ;
		}
		return null;
	}, $moduleValues);

	$ecc = in_array($_POST['ecc'], ['L', 'M', 'Q', 'H'], true) ? $_POST['ecc'] : 'L';

	$qro = new QROptions;

	$qro->version          = (int)$_POST['version'];
	$qro->eccLevel         = constant('chillerlan\\QRCode\\QRCode::ECC_'.$ecc);
	$qro->maskPattern      = (int)$_POST['maskpattern'];
	$qro->addQuietzone     = isset($_POST['quietzone']);
	$qro->quietzoneSize    = (int)$_POST['quietzonesize'];
	$qro->moduleValues     = $moduleValues;
	$qro->outputType       = $_POST['output_type'];
	$qro->scale            = (int)$_POST['scale'];
	$qro->imageTransparent = false;

	$qrcode = (new QRCode($qro))->render($_POST['inputstring']);

	if(in_array($_POST['output_type'], ['png', 'jpg', 'gif'])){
		$qrcode = '<img src="'.$qrcode.'" />';
	}
	elseif($_POST['output_type'] === 'text'){
		$qrcode = '<pre style="font-size: 75%; line-height: 1;">'.$qrcode.'</pre>';
	}
	elseif($_POST['output_type'] === 'json'){
		$qrcode = '<pre style="font-size: 75%; overflow-x: auto;">'.$qrcode.'</pre>';
	}

	send_response(['qrcode' => $qrcode]);
}
catch(\Exception $e){
	header('HTTP/1.1 500 Internal Server Error');
	send_response(['error' => $e->getMessage()]);
}

function send_response(array $response){
	header('Content-type: application/json;charset=utf-8;');
	echo json_encode($response);
	exit;
}

