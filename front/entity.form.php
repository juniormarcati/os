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

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_data = $_POST;
} else {
    $input_data = $_GET;
}

try {
    $entities_id = 0;
    if (isset($input_data['entities_id'])) {
        $entities_id = (int)$input_data['entities_id'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($input_data['update'])) {

        $rn = preg_replace('/[^0-9]/', '', trim($input_data['rn'] ?? ''));
        $company_name = PluginOsfreeEntity::normalizeCompanyName($input_data['company_name'] ?? '');

        $nameLength = function_exists('mb_strlen') ? mb_strlen($company_name) : strlen($company_name);
        if ($nameLength > 255) {
            Session::addMessageAfterRedirect(
                __('Company name is too long (maximum 255 characters)', 'osfree'),
                false,
                ERROR
            );
            Html::back();
            return;
        }

        global $DB;

        if (!$DB->tableExists('glpi_plugin_os_rn')) {
            throw new Exception('Tabela glpi_plugin_os_rn não existe');
        }

        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM' => 'glpi_plugin_os_rn',
            'WHERE' => ['entities_id' => $entities_id]
        ]);

        $recordExists = false;
        $existingId = null;
        foreach ($iterator as $row) {
            $recordExists = true;
            $existingId = $row['id'];
            break;
        }

        $current_time = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');

        $dataFields = [
            'entities_id' => $entities_id,
            'rn' => $rn,
            'company_name' => $company_name,
            'date_mod' => $current_time
        ];

        if ($recordExists) {
            
            $updateResult = $DB->update(
                'glpi_plugin_os_rn',
                $dataFields,
                ['id' => $existingId]
            );

            if (!$updateResult) {
                throw new Exception('Erro ao atualizar registro na tabela glpi_plugin_os_rn');
            }

        } else {
            
            $dataFields['date_creation'] = $current_time;
            
            $insertResult = $DB->insert('glpi_plugin_os_rn', $dataFields);

            if (!$insertResult) {
                throw new Exception('Erro ao inserir registro na tabela glpi_plugin_os_rn');
            }

        }

        Session::addMessageAfterRedirect(
            'Configuração salva com sucesso!',
            false,
            INFO
        );

    }

} catch (Exception $e) {

    Session::addMessageAfterRedirect(
        'Erro: ' . $e->getMessage(),
        false,
        ERROR
    );
}

Html::redirect('/front/entity.form.php?id=' . ($entities_id ?? 0));
