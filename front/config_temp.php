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

if (isset($_POST['name_form'])) {

    if (!isset($_SESSION['os_config_temp'])) {
        $_SESSION['os_config_temp'] = [
            'company' => [],
            'colors' => []
        ];
    }

    $existingLabelWidth = $_SESSION['os_config_temp']['company']['label_width'] ?? '';
    $existingLabelCustomText = $_SESSION['os_config_temp']['company']['label_custom_text'] ?? '';

    $_SESSION['os_config_temp']['company'] = [
        'name' => $_POST['name_form'],
        'cnpj' => $_POST['cnpj_form'],
        'address' => $_POST['address_form'],
        'phone' => $_POST['phone_form'],
        'city' => $_POST['city_form'],
        'site' => $_POST['site_form'],
        'currency' => $_POST['currency_form'] ?? 'BRL',
        'label_width' => $existingLabelWidth,
        'label_custom_text' => $existingLabelCustomText
    ];

    Html::redirect('index.php?step=2');
} else {
    Session::addMessageAfterRedirect('Erro: Dados incompletos', false, ERROR);
    Html::back();
}

