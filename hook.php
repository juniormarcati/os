<?php
/**
 * ------------------------------------------------------------------------
 * Plugin OS – Community Edition
 * Copyright (C) 2016-2026 Marcati
 * https://github.com/juniormarcati
 * ------------------------------------------------------------------------
 * This file is part of Plugin OS.
 *
 * Plugin OS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Plugin OS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Plugin OS. If not, see <https://www.gnu.org/licenses/>.
 * ------------------------------------------------------------------------
 *
 * @package   PluginOS
 * @author    Marcati
 * @copyright 2016-2026 Marcati
 * @license   AGPL-3.0-or-later
 * @link      https://github.com/juniormarcati/os
 * @since     2016
 * ------------------------------------------------------------------------
 */
if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}

function plugin_osfree_exec_query(string $sql, string $error_message = ''): void
{
    global $DB;

    $result = $DB->doQuery($sql);
    if ($result === false) {
        $db_error = method_exists($DB, 'error') ? $DB->error() : '';
        $message = $error_message ?: 'Failed to execute SQL query';
        if ($db_error !== '') {
            $message .= ' - ' . $db_error;
        }
        throw new RuntimeException($message);
    }
}

function plugin_osfree_install()
{
    global $DB;

    if (method_exists('Plugin', 'loadLang')) {
        Plugin::loadLang('osfree');
    }

    try {
        $config_table = "glpi_plugin_os_config";

        if (!$DB->tableExists($config_table)) {
            plugin_osfree_exec_query(
                "CREATE TABLE `{$config_table}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `entities_id` int(11) NOT NULL DEFAULT '0',
                `is_recursive` tinyint(1) NOT NULL DEFAULT '1',
                `name` varchar(255) NOT NULL DEFAULT '',
                `cnpj` varchar(20) NOT NULL DEFAULT '',
                `address` text,
                `phone` varchar(50) NOT NULL DEFAULT '',
                `city` varchar(100) NOT NULL DEFAULT '',
                `site` varchar(255) NOT NULL DEFAULT '',
                `label_width` int(3) NOT NULL DEFAULT '60',
                `label_custom_text` varchar(255) DEFAULT '',
                `date_mod` timestamp NULL DEFAULT NULL,
                `date_creation` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`),
                KEY `is_recursive` (`is_recursive`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "Erro ao criar tabela {$config_table}"
            );

            plugin_osfree_exec_query(
                "INSERT INTO `{$config_table}` (`id`, `entities_id`, `name`, `label_width`) VALUES (1, 0, 'Sua Empresa', 60)",
                "Erro ao inserir configuração padrão"
            );
        }

        $expected_columns = [
            'entities_id' => "int(11) NOT NULL DEFAULT '0'",
            'is_recursive' => "tinyint(1) NOT NULL DEFAULT '1'",
            'label_width' => "int(3) NOT NULL DEFAULT '60'",
            'label_custom_text' => "varchar(255) DEFAULT ''",
            'currency' => "varchar(3) NOT NULL DEFAULT 'BRL'",
            'date_mod' => "timestamp NULL DEFAULT NULL",
            'date_creation' => "timestamp NULL DEFAULT NULL"
        ];

        foreach ($expected_columns as $column => $definition) {
            if (!$DB->fieldExists($config_table, $column)) {
                plugin_osfree_exec_query(
                    "ALTER TABLE `{$config_table}` ADD COLUMN `{$column}` {$definition}",
                    "Erro ao adicionar coluna {$column}"
                );
            }
        }

        $rn_table = "glpi_plugin_os_rn";

        if (!$DB->tableExists($rn_table)) {
            plugin_osfree_exec_query(
                "CREATE TABLE `{$rn_table}` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `entities_id` int(11) NOT NULL DEFAULT '0',
                `rn` varchar(20) NOT NULL DEFAULT '',
                `company_name` varchar(255) NOT NULL DEFAULT '',
                `date_mod` timestamp NULL DEFAULT NULL,
                `date_creation` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_entities_id` (`entities_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "Erro ao criar tabela {$rn_table}"
            );
        } else {
            try {
                $existsLegacyIndex = $DB->query("SHOW INDEX FROM `{$rn_table}` WHERE Key_name = 'entities_id'");
                if ($existsLegacyIndex && $DB->numrows($existsLegacyIndex) > 0) {
                    plugin_osfree_exec_query(
                        "ALTER TABLE `{$rn_table}` DROP INDEX `entities_id`",
                        "Erro ao remover índice legacy entities_id"
                    );
                }
            } catch (Exception $e) {
            }

            if (!$DB->fieldExists($rn_table, 'company_name')) {
                plugin_osfree_exec_query(
                    "ALTER TABLE `{$rn_table}` ADD COLUMN `company_name` varchar(255) NOT NULL DEFAULT ''",
                    "Erro ao adicionar coluna company_name"
                );
            }

            if (!$DB->fieldExists($rn_table, 'date_creation')) {
                plugin_osfree_exec_query(
                    "ALTER TABLE `{$rn_table}` ADD COLUMN `date_creation` timestamp NULL DEFAULT NULL",
                    "Erro ao adicionar coluna date_creation"
                );
            }
            if (!$DB->fieldExists($rn_table, 'date_mod')) {
                plugin_osfree_exec_query(
                    "ALTER TABLE `{$rn_table}` ADD COLUMN `date_mod` timestamp NULL DEFAULT NULL",
                    "Erro ao adicionar coluna date_mod"
                );
            }

            try {
                plugin_osfree_exec_query(
                    "ALTER TABLE `{$rn_table}` ADD INDEX `idx_entities_id` (`entities_id`)",
                    "Erro ao criar índice idx_entities_id"
                );
            } catch (Exception $e) {
            }
        }

        $colors_table = "glpi_plugin_os_colors";

        if (!$DB->tableExists($colors_table)) {
            plugin_osfree_exec_query(
                "CREATE TABLE `{$colors_table}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `entities_id` int(11) NOT NULL DEFAULT '0',
                `is_recursive` tinyint(1) NOT NULL DEFAULT '1',
                `primary_color` varchar(7) NOT NULL DEFAULT '#2563EB',
                `secondary_color` varchar(7) NOT NULL DEFAULT '#0F172A',
                `accent_color` varchar(7) NOT NULL DEFAULT '#F59E0B',
                `light_color` varchar(7) NOT NULL DEFAULT '#F8FAFC',
                `date_mod` timestamp NULL DEFAULT NULL,
                `date_creation` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "Erro ao criar tabela {$colors_table}"
            );

            plugin_osfree_exec_query(
                "INSERT INTO `{$colors_table}` (`entities_id`, `primary_color`, `secondary_color`, `accent_color`, `light_color`) VALUES (0, '#2563EB', '#0F172A', '#F59E0B', '#F8FAFC')",
                "Erro ao inserir cores padrão"
            );
        }

        ProfileRight::deleteProfileRights(['plugin_osfree']);
        ProfileRight::addProfileRights(['plugin_osfree']);

        $profiles = getAllDataFromTable('glpi_profiles');
        foreach ($profiles as $profile) {
            if ($profile['name'] == 'Super-Admin') {
                ProfileRight::updateProfileRights($profile['id'], ['plugin_osfree' => ALLSTANDARDRIGHT]);

                plugin_osfree_protect_superadmin_rights($profile['id']);
            }
        }

        return true;
    } catch (Exception $e) {
        trigger_error('[osfree] install failed: ' . $e->getMessage(), E_USER_WARNING);
        return false;
    }
}

function plugin_osfree_uninstall()
{
    global $DB;

    try {
        $tables = [
            'glpi_plugin_os_config',
            'glpi_plugin_os_colors'
        ];

        foreach ($tables as $table) {
            if ($DB->tableExists($table)) {
                plugin_osfree_exec_query("DROP TABLE `{$table}`", "Erro ao remover tabela {$table}");
            }
        }

        ProfileRight::deleteProfileRights(['plugin_osfree']);

        $config = new Config();
        $config->deleteConfigurationValues('plugin:OS');

        return true;
    } catch (Exception $e) {
        trigger_error('[osfree] uninstall failed: ' . $e->getMessage(), E_USER_WARNING);
        return false;
    }
}

function plugin_osfree_update($current_version)
{
    global $DB;

    try {
        if (version_compare($current_version, '0.3.0', '<')) {
            plugin_osfree_migrate_to_030();
        }

        plugin_osfree_fix_rn_table();

        return true;
    } catch (Exception $e) {
        return false;
    }
}

function plugin_osfree_migrate_to_030()
{
    global $DB;

    $config_table = 'glpi_plugin_os_config';

    $audit_columns = [
        'date_mod' => 'timestamp NULL DEFAULT NULL',
        'date_creation' => 'timestamp NULL DEFAULT NULL'
    ];

    foreach ($audit_columns as $column => $definition) {
        if (!$DB->fieldExists($config_table, $column)) {
            $alter_query = "ALTER TABLE `{$config_table}` ADD COLUMN `{$column}` {$definition}";
            $DB->queryOrDie($alter_query, "Erro ao adicionar coluna {$column}");
        }
    }

    $tables_to_convert = ['glpi_plugin_os_config', 'glpi_plugin_os_rn'];
    foreach ($tables_to_convert as $table) {
        if ($DB->tableExists($table)) {
            $convert_query = "ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $DB->queryOrDie($convert_query, "Erro ao converter charset da tabela {$table}");
        }
    }
}

function plugin_osfree_fix_rn_table()
{
    global $DB;

    $rn_table = "glpi_plugin_os_rn";

    if (!$DB->tableExists($rn_table)) {
        return; 
    }

    try {
        $indexes_query = "SHOW INDEX FROM `{$rn_table}` WHERE Key_name = 'entities_id'";
        $indexes_result = $DB->query($indexes_query);

        if ($DB->numrows($indexes_result) > 0) {
            $DB->query("ALTER TABLE `{$rn_table}` DROP INDEX `entities_id`");
        }

        $normal_index_query = "SHOW INDEX FROM `{$rn_table}` WHERE Key_name = 'idx_entities_id'";
        $normal_index_result = $DB->query($normal_index_query);

        if ($DB->numrows($normal_index_result) == 0) {
            $DB->query("ALTER TABLE `{$rn_table}` ADD INDEX `idx_entities_id` (`entities_id`)");
        }

        if (!$DB->fieldExists($rn_table, 'date_creation')) {
            $DB->queryOrDie("ALTER TABLE `{$rn_table}` ADD COLUMN `date_creation` timestamp NULL DEFAULT NULL", "Erro ao adicionar coluna date_creation");
        }
        if (!$DB->fieldExists($rn_table, 'date_mod')) {
            $DB->queryOrDie("ALTER TABLE `{$rn_table}` ADD COLUMN `date_mod` timestamp NULL DEFAULT NULL", "Erro ao adicionar coluna date_mod");
        }

        if (!$DB->fieldExists($rn_table, 'company_name')) {
            $DB->queryOrDie("ALTER TABLE `{$rn_table}` ADD COLUMN `company_name` varchar(255) NOT NULL DEFAULT ''", "Erro ao adicionar coluna company_name");
        }
    } catch (Exception $e) {
    }
}

function plugin_osfree_item_update_profile(Profile $profile)
{
    $profileRight = new ProfileRight();
    $profile_id = $profile->getID();

    if ($profile->fields['name'] == 'Super-Admin') {
        plugin_osfree_protect_superadmin_rights($profile_id);
        return true; 
    }

    if (
        isset($_POST['_plugin_osfree_ticket_tab']) ||
        isset($_POST['_plugin_osfree_entity_tab']) ||
        isset($_POST['_plugin_osfree_profile_tab'])
    ) {

        $plugin_osfree_rights = 0;

        if (isset($_POST['_plugin_osfree_ticket_tab']) && $_POST['_plugin_osfree_ticket_tab'] == PluginOsfreeProfile::RIGHT_TICKET_TAB) {
            $plugin_osfree_rights |= PluginOsfreeProfile::RIGHT_TICKET_TAB;
        }

        if (isset($_POST['_plugin_osfree_entity_tab']) && $_POST['_plugin_osfree_entity_tab'] == PluginOsfreeProfile::RIGHT_ENTITY_TAB) {
            $plugin_osfree_rights |= PluginOsfreeProfile::RIGHT_ENTITY_TAB;
        }

        if (isset($_POST['_plugin_osfree_profile_tab']) && $_POST['_plugin_osfree_profile_tab'] == PluginOsfreeProfile::RIGHT_PROFILE_TAB) {
            $plugin_osfree_rights |= PluginOsfreeProfile::RIGHT_PROFILE_TAB;
        }

        $final_right = $plugin_osfree_rights;
    } else {
        $final_right = 0;
    }

    try {
        global $DB;
        $result = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $profile_id,
                'name' => 'plugin_osfree'
            ],
            'LIMIT' => 1
        ]);

        $existing = null;
        if (count($result) > 0) {
            foreach ($result as $row) {
                $existing = $row;
                break;
            }
        }

        if ($existing !== null && isset($existing['id'])) {
            $update_result = $profileRight->update([
                'id' => $existing['id'],
                'rights' => $final_right
            ]);

            if (!$update_result) {
                throw new Exception('Falha ao atualizar direito existente. ID: ' . $existing['id']);
            }
        } else {
            $add_result = $profileRight->add([
                'profiles_id' => $profile_id,
                'name' => 'plugin_osfree',
                'rights' => $final_right
            ]);

            if (!$add_result) {
                throw new Exception('Falha ao criar novo direito');
            }
        }

        if (
            isset($_SESSION['glpiactiveprofile']['id']) &&
            $_SESSION['glpiactiveprofile']['id'] == $profile_id
        ) {
            $_SESSION['glpiactiveprofile']['plugin_osfree'] = $final_right;

            if (class_exists('PluginOsfreeProfile')) {
                PluginOsfreeProfile::initProfile();
            }
        }

    } catch (Exception $e) {
        throw $e; 
    }

    return true;
}

function plugin_osfree_protect_superadmin_rights($profile_id)
{
    global $DB;

    try {
        $profile = new Profile();
        if (!$profile->getFromDB($profile_id) || $profile->fields['name'] != 'Super-Admin') {
            return; 
        }

        $profileRight = new ProfileRight();

        $result = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $profile_id,
                'name' => 'plugin_osfree'
            ],
            'LIMIT' => 1
        ]);

        $existing = null;
        if (count($result) > 0) {
            foreach ($result as $row) {
                $existing = $row;
                break;
            }
        }

        $superadmin_rights = class_exists('PluginOsfreeProfile') ? PluginOsfreeProfile::RIGHT_ALL : ALLSTANDARDRIGHT;

        if ($existing === null) {
            $add_result = $profileRight->add([
                'profiles_id' => $profile_id,
                'name' => 'plugin_osfree',
                'rights' => $superadmin_rights
            ]);

            if (!$add_result) {
            }
        } else if ($existing['rights'] != $superadmin_rights) {
            $update_result = $profileRight->update([
                'id' => $existing['id'],
                'rights' => $superadmin_rights
            ]);

            if (!$update_result) {
            }
        }

        if (
            isset($_SESSION['glpiactiveprofile']['id']) &&
            $_SESSION['glpiactiveprofile']['id'] == $profile_id
        ) {
            $_SESSION['glpiactiveprofile']['plugin_osfree'] = $superadmin_rights;
        }
    } catch (Exception $e) {
    }
}

function plugin_osfree_protect_superadmin(Profile $profile)
{
    if ($profile->fields['name'] == 'Super-Admin') {
        plugin_osfree_protect_superadmin_rights($profile->getID());
    }
    return true;
}

function plugin_osfree_verify_superadmin_rights()
{
    global $DB;

    try {
        $profiles = getAllDataFromTable('glpi_profiles', ['name' => 'Super-Admin']);

        foreach ($profiles as $profile) {
            $profileRight = new ProfileRight();
            $existing = $profileRight->getFromDBByCrit([
                'profiles_id' => $profile['id'],
                'name' => 'plugin_osfree'
            ]);

            if (empty($existing) || $existing['rights'] != ALLSTANDARDRIGHT) {
                plugin_osfree_protect_superadmin_rights($profile['id']);

            }
        }
    } catch (Exception $e) {
    }
}
