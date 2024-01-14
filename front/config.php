<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2024 by Junior Marcati
   https://github.com/juniormarcati/os
   ------------------------------------------------------------------------
   LICENSE
   This file is part of Plugin OS project.
   Plugin OS is free software: you can redistribute it and/or modify
   it under the terms of the GNU Affero General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.
   Plugin OS is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU Affero General Public License for more details.
   You should have received a copy of the GNU Affero General Public License
   along with Plugin OS. If not, see <http://www.gnu.org/licenses/>.
   ------------------------------------------------------------------------
   @package   Plugin OS
   @author    Junior Marcati
   @co-author
   @copyright Copyright (c) 2016-2024 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/os
   @since     2016
   ------------------------------------------------------------------------
 */
session_start();
include ("../../../inc/includes.php");
include ("../../../config/config.php");
$name_form = 	$_REQUEST["name_form"];
$cnpj_form = 	$_REQUEST["cnpj_form"]; 
$address_form =	$_REQUEST["address_form"];
$phone_form =	$_REQUEST["phone_form"];
$city_form =	$_REQUEST["city_form"];
$site_form = 	$_REQUEST["site_form"];
$query = "REPLACE INTO glpi_plugin_os_config (name, cnpj, address, phone, city, site)
        VALUES ('".$name_form."', '".$cnpj_form."', '".$address_form."', '".$phone_form."', '".$city_form."', '".$site_form."')";
$result = $DB->query($query);
echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=index.php'>";