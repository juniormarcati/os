<?php
/*
   ------------------------------------------------------------------------
   Plugin OS
   Copyright (C) 2016-2024
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
   ------------------------------------------------------------------------
*/

function plugin_os_install() {
   global $DB;

   // Inicia migração com versão 1.0
   $migration = new Migration(100);

   // Criação da tabela de configuração
   if (!$DB->tableExists('glpi_plugin_os_config')) {
      $query_conf = "
         CREATE TABLE `glpi_plugin_os_config` (
            `id` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL DEFAULT '0',
            `cnpj` VARCHAR(50) NOT NULL DEFAULT '0',
            `address` VARCHAR(255) NOT NULL DEFAULT '0',
            `phone` VARCHAR(255) NOT NULL DEFAULT '0',
            `city` VARCHAR(255) NOT NULL DEFAULT '0',
            `site` VARCHAR(255) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
      ";
      $migration->addPostQuery($query_conf);
   }

   // Criação da tabela de RN
   if (!$DB->tableExists('glpi_plugin_os_rn')) {
      $query_rn = "
         CREATE TABLE `glpi_plugin_os_rn` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `entities_id` INT(11) NOT NULL,
            `rn` VARCHAR(50) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY (`entities_id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
      ";
      $migration->addPostQuery($query_rn);
   }

   $migration->executeMigration();
   return true;
}


function plugin_os_uninstall() {
   global $DB;

   $migration = new Migration(100);

   if ($DB->tableExists('glpi_plugin_os_config')) {
      $migration->addPostQuery("DROP TABLE `glpi_plugin_os_config`;");
   }

   if ($DB->tableExists('glpi_plugin_os_rn')) {
      $migration->addPostQuery("DROP TABLE `glpi_plugin_os_rn`;");
   }

   $migration->executeMigration();
   return true;
}

function plugin_change_profile_os() {
   if (Session::haveRight('config', UPDATE)) {
      $_SESSION["glpi_plugin_os_profile"] = ['os' => 'w'];
   } else if (Session::haveRight('config', READ)) {
      $_SESSION["glpi_plugin_os_profile"] = ['os' => 'r'];
   } else {
      unset($_SESSION["glpi_plugin_os_profile"]);
   }
}
