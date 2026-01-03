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

if (isset($_POST['label_width'])) {
    
    $labelWidth = $_POST['label_width'] ?? '60';
    $labelCustomText = isset($_POST['label_custom_text']) ? $_POST['label_custom_text'] : '';
    
    if (!in_array($labelWidth, ['60', '70', '80'])) {
        Session::addMessageAfterRedirect('Erro: Largura da etiqueta inválida', false, ERROR);
        Html::back();
        return;
    }
    
    if (!isset($_SESSION['os_config_temp'])) {
        $_SESSION['os_config_temp'] = [
            'company' => [],
            'colors' => []
        ];
    }
    
    $_SESSION['os_config_temp']['company']['label_width'] = $labelWidth;
    $_SESSION['os_config_temp']['company']['label_custom_text'] = $labelCustomText;
    
    Html::redirect('index.php?step=5');
} else {
    Session::addMessageAfterRedirect('Erro: Dados da etiqueta incompletos', false, ERROR);
    Html::back();
}

