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
//plugin version
 define('PLUGIN_OS_VERSION', '0.2.0-beta6');
// Minimal GLPI version
define('PLUGIN_OS_MIN_GLPI', '9.4');
// Maximum GLPI version
define('PLUGIN_OS_MAX_GLPI', '10.1.1');

function plugin_init_os() {
  global $PLUGIN_HOOKS, $CFG_GLPI, $LANG;
  $PLUGIN_HOOKS['csrf_compliant']['os'] = true;

  Plugin::registerClass('PluginOsConfig', ['addtabon' => ['Ticket']]);
  Plugin::registerClass('PluginOsConfig', ['addtabon' => ['Entity']]);

  $_SESSION["glpi_plugin_os_profile"]['os'] = 'w';
  if (isset($_SESSION["glpi_plugin_os_profile"])) {
    $PLUGIN_HOOKS["menu_toadd"]['os'] = array('plugins'  => 'PluginOsConfig');
    }
}

// Config page
if (Session::haveRight('config', UPDATE)) {
  $PLUGIN_HOOKS['config_page']['os'] = 'front/index.php';
}
$PLUGIN_HOOKS['change_profile']['os'] = 'plugin_change_profile_os';

function plugin_version_os() {
  return [
    'name'          => 'OS',
    'version'       => PLUGIN_OS_VERSION ,
    'author'        => 'Junior Marcati',
    'license'       => 'AGPLv3+',
    'homepage'      => 'https://github.com/juniormarcati/os',
    'requirements'  => [
      'glpi'  => [
        'min' => PLUGIN_OS_MIN_GLPI,
        'max' => PLUGIN_OS_MAX_GLPI,
      ]
    ]
  ];
}