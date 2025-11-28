<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2022 by Junior Marcati
   https://github.com/juniormarcati/glpi_os
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
   @copyright Copyright (c) 2016-2022 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/glpi_os
   @since     2016
   ------------------------------------------------------------------------
 */

//plugin version
 define('PLUGIN_OS_VERSION', '0.3.0');
// Minimal GLPI version
define('PLUGIN_OS_MIN_GLPI', '11.0');
// Maximum GLPI version
define('PLUGIN_OS_MAX_GLPI', '11.99');

function plugin_init_os() {
  global $PLUGIN_HOOKS, $LANG;
  $PLUGIN_HOOKS['csrf_compliant']['os'] = true;

  Plugin::registerClass('PluginOsConfig', ['addtabon' => ['Ticket']]);
  Plugin::registerClass('PluginOsConfig', ['addtabon' => ['Entity']]);

  $PLUGIN_HOOKS["menu_toadd"]['os'] = array('plugins'  => 'PluginOsConfig');
  $PLUGIN_HOOKS['config_page']['os'] = 'front/index.php';
}

function plugin_version_os(){
  global $DB, $LANG;
  return [
    'name'          => __('OS','os'),
    'version'       => PLUGIN_OS_VERSION ,
    'author'        => '<a href="mailto:junior@marcati.com.br"> Júnior Marcati </b> </a>',
    'license'       => 'AGPLv3+',
    'homepage'      => 'https://github.com/juniormarcati/glpi_os/',
    'requirements'  => [
      'glpi'        => [
        'min'       => PLUGIN_OS_MIN_GLPI,
        'max'       => PLUGIN_OS_MAX_GLPI,
      ]
    ]
  ];
}