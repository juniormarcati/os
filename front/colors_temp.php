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
    
    $primaryColor = filter_var($_POST['primary_color'], FILTER_SANITIZE_STRING);
    $secondaryColor = filter_var($_POST['secondary_color'], FILTER_SANITIZE_STRING);
    $accentColor = filter_var($_POST['accent_color'], FILTER_SANITIZE_STRING);
    $lightColor = filter_var($_POST['light_color'], FILTER_SANITIZE_STRING);
    
    $hexColorPattern = '/^#[a-f0-9]{6}$/i';
    if (!preg_match($hexColorPattern, $primaryColor) ||
        !preg_match($hexColorPattern, $secondaryColor) ||
        !preg_match($hexColorPattern, $accentColor) ||
        !preg_match($hexColorPattern, $lightColor)) {
        Session::addMessageAfterRedirect('Erro: Formato de cor inválido', false, ERROR);
        Html::back();
        return;
    }
    
    if (!isset($_SESSION['os_config_temp'])) {
        $_SESSION['os_config_temp'] = [
            'company' => [],
            'colors' => []
        ];
    }
    
    $_SESSION['os_config_temp']['colors'] = [
        'primary_color' => $primaryColor,
        'secondary_color' => $secondaryColor,
        'accent_color' => $accentColor,
        'light_color' => $lightColor
    ];
    
    Html::redirect('index.php?step=4');
} else {
    Session::addMessageAfterRedirect('Erro: Dados incompletos', false, ERROR);
    Html::back();
}

