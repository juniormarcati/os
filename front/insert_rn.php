<?php
include("../../../inc/includes.php");
include("../../../inc/config.php");
global $DB;

Session::checkLoginUser();
Session::checkRight("profile", READ);

$ent_id = $_POST["id"] ?? 0;
$rn = $_POST["rn"] ?? null;

if ($ent_id && $rn !== null && $rn !== "") {
    $DB->update('glpi_plugin_os_rn', ['rn' => $rn], ['entities_id' => $ent_id], true);
    echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";
} elseif ($ent_id && $rn === "") {
    $DB->delete('glpi_plugin_os_rn', ['entities_id' => $ent_id]);
    echo "<meta HTTP-EQUIV='refresh' CONTENT='0;URL=".$CFG_GLPI['root_doc']."/front/entity.form.php?id=".$ent_id."'>";
}
