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
include('../../../inc/includes.php');
global $DB;
Session::checkRight("config", UPDATE);

if (!isset($_SESSION['os_config_temp'])) {
    Session::addMessageAfterRedirect(__('Configuration data not found!', 'osfree'));
    Html::redirect('index.php');
    exit;
}

$companyData = $_SESSION['os_config_temp']['company'] ?? [];
$colorsData = $_SESSION['os_config_temp']['colors'] ?? [];

try {
    $DB->beginTransaction();

    if (!empty($companyData)) {
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_os_config',
            'WHERE' => ['id' => 1]
        ]);

        $recordExists = false;
        foreach ($iterator as $row) {
            $recordExists = true;
            break;
        }

        $companyFields = [
            'name' => $companyData['name'] ?? '',
            'cnpj' => $companyData['cnpj'] ?? '',
            'address' => $companyData['address'] ?? '',
            'phone' => $companyData['phone'] ?? '',
            'city' => $companyData['city'] ?? '',
            'site' => $companyData['site'] ?? '',
            'currency' => $companyData['currency'] ?? 'BRL',
            'label_width' => (int)($companyData['label_width'] ?? 60),
            'label_custom_text' => $companyData['label_custom_text'] ?? '',
            'date_mod' => $_SESSION['glpi_currenttime'] 
        ];

        if ($recordExists) {
            $updateResult = $DB->update(
                'glpi_plugin_os_config',
                $companyFields,
                ['id' => 1]  
            );

            if (!$updateResult) {
                throw new Exception(__('Error updating company settings', 'osfree'));
            }
        } else {
            $companyFields['id'] = 1;  
            $companyFields['entities_id'] = 0; 
            $companyFields['is_recursive'] = 1; 
            $companyFields['date_creation'] = $_SESSION['glpi_currenttime']; 

            $insertResult = $DB->insert('glpi_plugin_os_config', $companyFields);

            if (!$insertResult) {
                throw new Exception(__('Error inserting company settings', 'osfree'));
            }
        }
    }

    if (!empty($colorsData)) {
        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_os_colors',
            'LIMIT' => 1
        ]);

        $recordExists = false;
        $existingId = null;
        foreach ($iterator as $row) {
            $recordExists = true;
            $existingId = $row['id'];
            break;
        }

        $colorFields = [
            'primary_color' => $colorsData['primary_color'] ?? '#007bff',
            'secondary_color' => $colorsData['secondary_color'] ?? '#6c757d',
            'accent_color' => $colorsData['accent_color'] ?? '#28a745',
            'light_color' => $colorsData['light_color'] ?? '#f8f9fa'
        ];

        if ($recordExists) {
            $updateResult = $DB->update(
                'glpi_plugin_os_colors',
                $colorFields,
                ['id' => $existingId]  
            );

            if (!$updateResult) {
                throw new Exception(__('Error updating color settings', 'osfree'));
            }
        } else {
            $insertResult = $DB->insert('glpi_plugin_os_colors', $colorFields);

            if (!$insertResult) {
                throw new Exception(__('Error inserting color settings', 'osfree'));
            }
        }
    }

    $DB->commit();

    unset($_SESSION['os_config_temp']);

    Session::addMessageAfterRedirect(__('Settings saved successfully!', 'osfree'));
} catch (Exception $e) {
    $DB->rollback();

    Session::addMessageAfterRedirect(__('Error saving settings: ', 'osfree') . $e->getMessage());
}

Html::redirect('index.php');

