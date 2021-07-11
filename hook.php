<?php

/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2021 by Junior Marcati
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
   @copyright Copyright (c) 2016-2021 OS Plugin Development team
   @license   AGPL License 3.0 or (at your option) any later version
              http://www.gnu.org/licenses/agpl-3.0-standalone.html
   @link      https://github.com/juniormarcati/glpi_os
   @since     2016
   ------------------------------------------------------------------------
 */
function plugin_os_install() {
	global $DB;
	
          $query_conf = "CREATE TABLE IF NOT EXISTS `glpi_plugin_os_config` (
          `id` int(1) unsigned NOT NULL default '1',
          `name` varchar(255) NOT NULL default '0',
          `cnpj`  varchar(50) NOT NULL default '0',
          `address` varchar(50) NOT NULL default '0',
          `phone` varchar(255) NOT NULL default '0',
          `city`  varchar(255) NOT NULL default '0',
          `site`  varchar(50) NOT NULL default '0',
	  PRIMARY KEY (`id`) )
	  ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

       $DB->query($query_conf) or die("error creating table glpi_plugin_os_config " . $DB->error());

       return true;
}
function plugin_os_uninstall(){
	global $DB;
	$drop_config = "DROP TABLE glpi_plugin_os_config";
	$DB->query($drop_config);
	return true;
}
?>
