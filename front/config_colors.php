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

if (isset($_POST['primary_color']) && isset($_POST['secondary_color']) &&
    isset($_POST['accent_color']) && isset($_POST['light_color'])) {
    
    $primaryColor = Html::cleanInputText($_POST['primary_color']);
    $secondaryColor = Html::cleanInputText($_POST['secondary_color']);
    $accentColor = Html::cleanInputText($_POST['accent_color']);
    $lightColor = Html::cleanInputText($_POST['light_color']);
    
    $hexColorPattern = '/^#[a-f0-9]{6}$/i';
    if (!preg_match($hexColorPattern, $primaryColor) ||
        !preg_match($hexColorPattern, $secondaryColor) ||
        !preg_match($hexColorPattern, $accentColor) ||
        !preg_match($hexColorPattern, $lightColor)) {

        Session::addMessageAfterRedirect('Erro: Formato de cor inválido', false, ERROR);
        Html::back();
        return;
    }
    
    try {
        $DB->beginTransaction();
        
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
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'accent_color' => $accentColor,
            'light_color' => $lightColor
        ];
        
        if ($recordExists) {
            $updateResult = $DB->update(
                'glpi_plugin_os_colors',
                $colorFields,
                ['id' => $existingId]
            );
            
            if (!$updateResult) {
                throw new Exception('Erro ao atualizar configurações de cores');
            }

        } else {
            $insertResult = $DB->insert('glpi_plugin_os_colors', $colorFields);
            
            if (!$insertResult) {
                throw new Exception('Erro ao inserir configurações de cores');
            }

        }
        
        $DB->commit();
        
        Session::addMessageAfterRedirect('Cores configuradas com sucesso', false, INFO);
        
        Html::redirect('index.php?step=4');
        
    } catch (Exception $e) {
        $DB->rollback();
        
        Session::addMessageAfterRedirect('Erro ao salvar configurações de cores: ' . $e->getMessage(), false, ERROR);
        Html::back();
    }
    
} else {

    Session::addMessageAfterRedirect('Erro: Dados incompletos', false, ERROR);
    Html::back();
}

