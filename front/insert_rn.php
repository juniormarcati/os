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
include ("../../../inc/includes.php");
include ("../../../inc/config.php");

Session::checkLoginUser();
Session::checkRight("profile", READ);

$ent_id =	$_POST["id"];

if(isset($_POST["rn"])) {
	$rn = 	$_POST["rn"]; 
	
	$query = "SELECT name FROM glpi_entities WHERE id = ".$ent_id;
	$result = $DB->query($query) or die ("error insert");
	
	$location = $DB->result($result,0,'name');

	$insert = "
		INSERT INTO glpi_plugin_os_rn (entities_id, rn) 
		VALUES ('$ent_id', '$rn') 
		ON DUPLICATE KEY UPDATE rn='$rn'";

    $DB->query($insert) or die ("Error inserting rn");
	
	echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";
}

if($_POST["rn"] == "") {
	
	$query = "DELETE FROM glpi_plugin_os_rn WHERE entities_id = ".$_POST["id"];
	$DB->query($query) or die ("error removing rn");
	
	echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";	
}