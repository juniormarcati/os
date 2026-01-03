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

Session::checkRight("config", UPDATE);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Http::displayErrorAndDie(__('Method Not Allowed', 'osfree'), 405);
}

Session::checkCSRF($_POST);

try {
    $config = new PluginOsfreeConfig();
    
    $result = $config->saveConfig($_POST);
    
    if ($result) {
        Session::addMessageAfterRedirect(
            __('Configuration saved successfully', 'osfree'), 
            false, 
            INFO
        );
        
        Log::history(
            1,
            'PluginOsfreeConfig',
            [0, '', sprintf(__('Configuration changed by %s', 'osfree'), $_SESSION['glpiname'])]
        );
    } else {
        Session::addMessageAfterRedirect(
            __('Error saving configuration', 'osfree'), 
            false, 
            ERROR
        );
    }

} catch (Exception $e) {
    Session::addMessageAfterRedirect(
        __('Internal server error', 'osfree'), 
        false, 
        ERROR
    );
}

Html::back();

