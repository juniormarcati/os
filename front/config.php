<?php
session_start();
// includes
include ("../../../inc/includes.php");
include ("../../../config/config.php");
// configuracoes trazidas do index
$name_form = 	$_REQUEST["name_form"]; 
$address_form =	$_REQUEST["address_form"];
$phone_form =	$_REQUEST["phone_form"];
$city_form =	$_REQUEST["city_form"];
$textcolor_form =	$_REQUEST["textcolor_form"];
$color_form =	$_REQUEST["color_form"];

$query = "REPLACE INTO glpi_plugin_os_config (name, address, phone, city, textcolor, color)
        VALUES ('".$name_form."', '".$address_form."', '".$phone_form."', '".$city_form."', '".$textcolor_form."', '".$color_form."')";
$result = $DB->query($query);

echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=os.php'>";
?>

