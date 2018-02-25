<?php
session_start();
include ("../../../inc/includes.php");
include ("../../../config/config.php");
$name_form = 	$_REQUEST["name_form"];
$cnpj_form = 	$_REQUEST["cnpj_form"]; 
$address_form =	$_REQUEST["address_form"];
$phone_form =	$_REQUEST["phone_form"];
$city_form =	$_REQUEST["city_form"];
$textcolor_form =	$_REQUEST["textcolor_form"];
$color_form =	$_REQUEST["color_form"];
$site_form = 	$_REQUEST["site_form"];
$query = "REPLACE INTO glpi_plugin_os_config (name, cnpj, address, phone, city, textcolor, color, site)
        VALUES ('".$name_form."', '".$cnpj_form."', '".$address_form."', '".$phone_form."', '".$city_form."', '".$textcolor_form."', '".$color_form."', '".$site_form."')";
$result = $DB->query($query);
echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=os.php'>";
?>