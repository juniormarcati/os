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

if (!defined('GLPI_ROOT')) {
    die("Access denied.");
}
if (!defined('PLUGIN_OS_DIR')) {
    define('PLUGIN_OS_DIR', __DIR__ . '/..');
}
Session::checkLoginUser();

if (!Session::haveRight("config", UPDATE)) {
    Html::displayRightError();
    exit;
}

global $DB;
$Plugin = $DB->request('glpi_plugin_os_config')->current();

$csrf_token = Session::getNewCSRFToken();

$EmpresaPlugin  = $Plugin['name'] ?? '';
$CnpjPlugin     = $Plugin['cnpj'] ?? '';
$EnderecoPlugin = $Plugin['address'] ?? '';
$TelefonePlugin = $Plugin['phone'] ?? '';
$CidadePlugin   = $Plugin['city'] ?? '';
$SitePlugin     = $Plugin['site'] ?? '';

Html::header(
    __('OS', 'os'),
    $_SERVER['PHP_SELF'],
    'plugins',
    'os',
    'config'
);

echo "<div style='background:#fff; margin:auto; width:60%; border: 1px solid #ddd; padding-bottom:25px;'>";

echo "<div align='center'>";
echo "<a href='http://glpi-os.sourceforge.net' target='_blank'>";
$logo_file = 'logo_os.png';
$physical_path = '../pics/' . $logo_file;
$web_path  = $CFG_GLPI['root_doc'] . '/plugins/os/front/logo.php';

echo "<div align='center'>";
echo "<a href='http://glpi-os.sourceforge.net' target='_blank'>";

if (file_exists($physical_path)) {
    echo "<img src='" . $web_path . "' alt='Logo OS' style='max-width:300px; height:auto;' />";
} else {
    echo "<p>Logotipo ainda não enviado.</p>";
}

echo "</a>";
echo "</div>";

echo "</a>";
echo "</div>";

echo "<h1 style='text-align:center;'>GLPI_OS: Configuração</h1>";

echo "<h3 style='text-align:center;'>PASSO 1 - CABEÇALHO</h3>";
echo "<p style='text-align:center;'>Informações da Empresa.</p>";

echo "<form action='config.php' method='post'>";

echo "<table style='width:500px; margin:auto;'>";
echo "<tr><td>Nome da sua empresa:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='name_form' value='" . htmlspecialchars($EmpresaPlugin) . "'></td></tr>";

echo "<tr><td>CNPJ:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='cnpj_form' value='" . htmlspecialchars($CnpjPlugin) . "'></td></tr>";

echo "<tr><td>Endereço de sua empresa:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='address_form' value='" . htmlspecialchars($EnderecoPlugin) . "'></td></tr>";

echo "<tr><td>Telefone:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='phone_form' value='" . htmlspecialchars($TelefonePlugin) . "'></td></tr>";

echo "<tr><td>Cidade/Estado:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='city_form' value='" . htmlspecialchars($CidadePlugin) . "'></td></tr>";

echo "<tr><td>Site:</td>";
echo "<td><input type='text' size='35' maxlength='256' name='site_form' value='" . htmlspecialchars($SitePlugin) . "'></td></tr>";

echo "<tr><td colspan='2' style='text-align:center;'>";
echo "<input type='submit' class='submit' value='Salvar' name='enviar'>";
echo "</td></tr>";
echo "</table>";
echo Html::closeForm();

echo "<h3 style='text-align:center;'>PASSO 2 - Logotipo da OS</h3>";
echo "<p style='text-align:center;'>Faça UPLOAD do logotipo (300x100) que será utilizado na OS. (obs: pasta pics precisa ter permissão de escrita.)</p>";

echo "<form method='post' enctype='multipart/form-data' action='insert_logo.php'>";

echo "<table style='width:500px; margin:auto;'>";
echo "<tr><td>Selecione uma imagem: <input name='arquivo' type='file' accept='.png' required></td></tr>";
echo "<tr><td colspan='2' style='text-align:center;'>";
echo "<input type='submit' class='submit' value='Enviar' name='enviar'>";
echo "</td></tr>";
echo "</table>";
echo Html::closeForm();

echo "<div style='text-align:center; margin-top:20px;'>";
echo "<a href='javascript:history.back();' class='vsubmit'> Voltar </a>";
echo "</div>";

echo "</div>";

Html::footer();
