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

Session::checkRight('profile', UPDATE);

if (!Session::haveRight('plugin_osfree', READ) && !Session::haveRight('config', UPDATE)) {
    Session::addMessageAfterRedirect(
        __('Access denied. You do not have permission to manage this plugin.', 'osfree'),
        false,
        ERROR
    );
    Html::redirect(Toolbox::getItemTypeFormURL('Profile'));
}

if (isset($_POST['update']) && isset($_POST['id'])) {
    $profile = new Profile();
    $profile_id = (int)$_POST['id'];

    if ($profile->getFromDB($profile_id)) {

        $isSuperAdmin = ($profile->fields['name'] == 'Super-Admin');

        if ($isSuperAdmin) {
            Session::addMessageAfterRedirect(
                __('Super-Admin rights cannot be modified for this plugin', 'osfree'),
                false,
                WARNING
            );
            Html::redirect($profile->getLinkURL());
            exit;
        }

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

        try {
            global $DB;
            $profileRight = new ProfileRight();

            $result = $DB->request([
                'FROM' => 'glpi_profilerights',
                'WHERE' => [
                    'profiles_id' => $profile_id,
                    'name' => 'plugin_osfree'
                ],
                'LIMIT' => 1
            ]);

            $existing_right = null;
            if (count($result) > 0) {
                foreach ($result as $row) {
                    $existing_right = $row;
                    break;
                }
            }

            if ($existing_right !== null && isset($existing_right['id'])) {

                $update_result = $profileRight->update([
                    'id' => $existing_right['id'],
                    'rights' => $plugin_osfree_rights
                ]);

                if (!$update_result) {
                    throw new Exception('Falha ao atualizar direito existente. ID: ' . $existing_right['id']);
                }

            } else {

                $add_result = $profileRight->add([
                    'profiles_id' => $profile_id,
                    'name' => 'plugin_osfree',
                    'rights' => $plugin_osfree_rights
                ]);

                if (!$add_result) {
                    throw new Exception('Falha ao criar novo direito');
                }

            }

            if (
                isset($_SESSION['glpiactiveprofile']['id']) &&
                $_SESSION['glpiactiveprofile']['id'] == $profile_id
            ) {
                $_SESSION['glpiactiveprofile']['plugin_osfree'] = $plugin_osfree_rights;

                if (class_exists('PluginOsfreeProfile')) {
                    PluginOsfreeProfile::initProfile();
                }

                if (function_exists('plugin_change_profile_osfree')) {
                    plugin_change_profile_osfree();
                }
            }

            Session::addMessageAfterRedirect(__('Rights updated successfully', 'osfree'));

            Html::redirect($profile->getLinkURL());
        } catch (Exception $e) {

            Session::addMessageAfterRedirect(
                __('Error updating rights', 'osfree') . ': ' . $e->getMessage(),
                false,
                ERROR
            );
            Html::redirect($profile->getLinkURL());
        }
    } else {
        Session::addMessageAfterRedirect(
            __('Profile not found or access denied', 'osfree'),
            false,
            ERROR
        );
        Html::redirect(Toolbox::getItemTypeFormURL('Profile'));
    }
} else {

    if (isset($_POST['id'])) {
        $profile = new Profile();
        $profile_id = (int)$_POST['id'];

        if ($profile->getFromDB($profile_id)) {
            Html::redirect($profile->getLinkURL());
        } else {
            Html::redirect(Toolbox::getItemTypeFormURL('Profile'));
        }
    } else {
        Session::addMessageAfterRedirect(
            __('Invalid request data', 'osfree'),
            false,
            WARNING
        );
        Html::redirect(Toolbox::getItemTypeFormURL('Profile'));
    }
}

