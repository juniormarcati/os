<?php
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
		
		
		// INSERT INTO glpi_plugin_os_rn (entities_id, rn) 
		// VALUES ('$ent_id', '$rn') 
		// ON DUPLICATE KEY UPDATE rn=('$rn')";			 
	
	$DB->query($insert) or die ("Error inserting rn");
	
	echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";
}

if($_POST["rn"] == "") {
	
	$query = "DELETE FROM glpi_plugin_os_rn WHERE entities_id = ".$_POST["id"];
	$DB->query($query) or die ("error removing rn");
	
	echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";	
}
?>