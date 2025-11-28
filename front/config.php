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
Session::checkLoginUser();
if (!Session::haveRight("config", UPDATE)) {
    Html::displayRightError();
    exit;
}

global $DB;

$data = [
    'name'    => $_POST["name_form"] ?? '',
    'cnpj'    => $_POST["cnpj_form"] ?? '',
    'address' => $_POST["address_form"] ?? '',
    'phone'   => $_POST["phone_form"] ?? '',
    'city'    => $_POST["city_form"] ?? '',
    'site'    => $_POST["site_form"] ?? ''
];

$existing = $DB->request('glpi_plugin_os_config')->current();
if ($existing) {
    $DB->update('glpi_plugin_os_config', $data, ['id' => $existing['id']]);
} else {
    $DB->insert('glpi_plugin_os_config', $data);
}

Session::addMessageAfterRedirect("Configuração salva com sucesso.", true, INFO);
Html::redirect('index.php');