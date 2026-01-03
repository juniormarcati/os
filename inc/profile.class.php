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

class PluginOsfreeProfile extends CommonDBTM
{
    const RIGHT_TICKET_TAB = 1;    
    const RIGHT_ENTITY_TAB = 2;    
    const RIGHT_PROFILE_TAB = 4;   

    const RIGHT_ALL = 7; 

    static $rightname = 'plugin_osfree';

    static function haveRight($right)
    {
        if (!isset($_SESSION['glpiactiveprofile']['plugin_osfree'])) {
            return false;
        }

        $current_rights = (int)$_SESSION['glpiactiveprofile']['plugin_osfree'];
        return ($current_rights & $right) == $right;
    }

    static function canAccessTicketTab()
    {
        if (isset($_SESSION['glpiactiveprofile']['name']) && $_SESSION['glpiactiveprofile']['name'] == 'Super-Admin') {
            return true;
        }

        return self::haveRight(self::RIGHT_TICKET_TAB);
    }

static function canAccessEntityTab()
{
    if (isset($_SESSION['glpiactiveprofile']['name']) && $_SESSION['glpiactiveprofile']['name'] == 'Super-Admin') {
        return true;
    }
    
    return self::haveRight(self::RIGHT_ENTITY_TAB);
}

    static function canAccessProfileTab()
    {
        return self::haveRight(self::RIGHT_PROFILE_TAB);
    }

    static function getPluginRights($interface = 'central')
    {
        $rights = [
            'ticket_tab' => [
                'short' => __('Ticket Tab', 'osfree'),
                'long' => __('Access to service order tab in tickets', 'osfree'),
                'value' => self::RIGHT_TICKET_TAB
            ],
            'entity_tab' => [
                'short' => __('Entity Tab', 'osfree'),
                'long' => __('Access to service order tab in entities', 'osfree'),
                'value' => self::RIGHT_ENTITY_TAB
            ],
            'profile_tab' => [
                'short' => __('Profile Tab', 'osfree'),
                'long' => __('Access to service order configuration in profiles', 'osfree'),
                'value' => self::RIGHT_PROFILE_TAB
            ]
        ];

        return $rights;
    }

    static function initProfile()
    {
        global $DB;

        if (!isset($_SESSION['glpiactiveprofile'])) {
            return;
        }

        $profile_id = $_SESSION['glpiactiveprofile']['id'];

        $result = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $profile_id,
                'name' => 'plugin_osfree'
            ],
            'LIMIT' => 1
        ]);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $_SESSION['glpiactiveprofile']['plugin_osfree'] = $row['rights'];
                break;
            }
        } else {
            $_SESSION['glpiactiveprofile']['plugin_osfree'] = 0;
        }

    }

    function rawSearchOptions()
    {
        $tab = [];
        $tab[] = [
            'id'   => 'common',
            'name' => __('Work Order', 'osfree')
        ];
        $tab[] = [
            'id'        => '2',
            'table'     => $this->getTable(),
            'field'     => 'use',
            'linkfield' => 'id',
            'datatype'  => 'bool'
        ];
        return $tab;
    }

    function showForm($ID, $options = [])
    {
        if (!Session::haveRight('plugin_osfree', READ) && !Session::haveRight('config', UPDATE)) {
            echo "<div class='center'>";
            echo "<div class='alert alert-danger'>";
            echo "<i class='fas fa-ban'></i> ";
            echo __('Access denied. You do not have permission to manage this plugin.', 'osfree');
            echo "</div>";
            echo "</div>";
            return;
        }

        $profile = new Profile();
        $canedit = Session::haveRightsOr('profile', [CREATE, UPDATE, PURGE]);

        $profile->getFromDB($ID);
        $isSuperAdmin = ($profile->fields['name'] == 'Super-Admin');

        if ($canedit) {
            echo "<form action='" . Plugin::getWebDir('osfree') . "/front/profile.form.php' method='post'>";
            echo "<input type='hidden' name='_glpi_csrf_token' value='" . Session::getNewCSRFToken() . "'>";
        }

        global $DB;
        $current_rights = 0;

        $result = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $ID,
                'name' => 'plugin_osfree'
            ],
            'LIMIT' => 1
        ]);

        if (count($result) > 0) {
            foreach ($result as $row) {
                $current_rights = (int)$row['rights'];
                break;
            }
        }

        if ($isSuperAdmin) {
            $current_rights = self::RIGHT_ALL;
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='2' class='center b'>" . __('Service Order Tab Permissions', 'osfree') . "</th></tr>";

        if ($isSuperAdmin) {
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='2' class='center'>";
            echo "<div style='color: #2ecc71; font-weight: bold; padding: 10px;'>";
            echo "<i class='fas fa-shield-alt'></i> " . __('Super-Admin has full access to all plugin features', 'osfree');
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        } else {
            $available_rights = self::getPluginRights();

            foreach ($available_rights as $key => $right) {
                $checked = ($current_rights & $right['value']) ? true : false;
                $input_name = "_plugin_osfree_{$key}";

                echo "<tr class='tab_bg_1'>";
                echo "<td style='width: 200px; vertical-align: top; padding: 10px;'>";
                echo "<label for='{$input_name}' style='display: flex; align-items: center; cursor: pointer; font-weight: 500;'>";

                Html::showCheckbox([
                    'name' => $input_name,
                    'id' => $input_name,
                    'value' => $right['value'],
                    'checked' => $checked
                ]);

                echo "<span style='margin-left: 8px;'>{$right['short']}</span>";
                echo "</label>";
                echo "</td>";

                echo "<td style='color: #666; font-size: 13px; padding: 10px;'>";
                echo $right['long'];
                echo "</td>";
                echo "</tr>";
            }
        }

        echo "</table>\n";

        if ($canedit) {
            echo "<div class='center' style='margin-top: 10px;'>";
            echo Html::hidden('id', ['value' => $ID]);

            if (!$isSuperAdmin) {
                echo Html::submit(_sx('button', 'Save'), [
                    'name'  => 'update',
                    'class' => 'btn btn-primary',
                    'icon'  => 'ti ti-device-floppy'
                ]);

            } else {
                echo "<p style='font-style: italic; color: #666; margin-top: 10px;'>";
                echo __('Super-Admin rights cannot be modified', 'osfree');
                echo "</p>";
            }
            echo "</div>\n";
            Html::closeForm();
        }
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            if (class_exists('PluginOsfreeProfile') && !PluginOsfreeProfile::canAccessProfileTab()) {
                return '';
            }

            if (Session::haveRight('plugin_osfree', READ) || Session::haveRight('config', UPDATE)) {
                return __('Work Order', 'osfree');
            }
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            if (class_exists('PluginOsfreeProfile') && !PluginOsfreeProfile::canAccessProfileTab()) {
                echo "<div class='center'>";
                echo "<div class='alert alert-warning'>";
                echo "<i class='fas fa-exclamation-triangle'></i> ";
                echo __('You do not have permission to access this tab.', 'osfree');
                echo "</div>";
                echo "</div>";
                return false;
            }

            if (!Session::haveRight('plugin_osfree', READ) && !Session::haveRight('config', UPDATE)) {
                echo "<div class='center'>";
                echo "<div class='alert alert-warning'>";
                echo "<i class='fas fa-exclamation-triangle'></i> ";
                echo __('You do not have permission to manage this plugin rights.', 'osfree');
                echo "</div>";
                echo "</div>";
                return false;
            }

            $prof = new self();
            $ID = $item->getField('id');
            $prof->showForm($ID);
        }
        return true;
    }

    static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false)
    {
        $profileRight = new ProfileRight();
        $dbu = new DbUtils();

        foreach ($rights as $right => $value) {
            if ($dbu->countElementsInTable(
                'glpi_profilerights',
                [
                    'profiles_id' => $profiles_id,
                    'name'        => $right
                ]
            ) && $drop_existing) {
                $profileRight->deleteByCriteria([
                    'profiles_id' => $profiles_id,
                    'name'        => $right
                ]);
            }

            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                [
                    'profiles_id' => $profiles_id,
                    'name'        => $right
                ]
            )) {
                $myright = [
                    'profiles_id' => $profiles_id,
                    'name'        => $right,
                    'rights'      => $value
                ];
                $profileRight->add($myright);
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    static function createFirstAccess($ID)
    {
        if ($ID > 0) {
            $profile = new Profile();
            if ($profile->getFromDB($ID) && $profile->fields['name'] == 'Super-Admin') {
                self::addDefaultProfileInfos($ID, ['plugin_osfree' => self::RIGHT_ALL], true);
            } else {
                self::addDefaultProfileInfos($ID, ['plugin_osfree' => 0], true);
            }
        }
    }

    static function getAllRights($all = false)
    {
        $rights = [[
            'itemtype' => 'PluginOsfree',
            'label'    => __('Work Order', 'osfree'),
            'field'    => 'plugin_osfree'
        ]];
        return $rights;
    }

    static function removeRightsFromSession()
    {
        foreach (self::getAllRights(true) as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }

        if (isset($_SESSION['glpiactiveprofile']['plugin_osfree'])) {
            unset($_SESSION['glpiactiveprofile']['plugin_osfree']);
        }
    }

    static function install(Migration $mig)
    {
        global $DB;

        ProfileRight::addProfileRights(['plugin_osfree']);

        $profiles = $DB->request('glpi_profiles', ['name' => 'Super-Admin']);
        foreach ($profiles as $profile) {
            self::createFirstAccess($profile['id']);
        }

        if (isset($_SESSION['glpiactiveprofile']['id'])) {
            self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
        }

        self::initProfile();
    }

    static function uninstall()
    {
        global $DB;

        $rights_to_remove = ['plugin_osfree'];

        foreach ($rights_to_remove as $right) {
            $DB->delete('glpi_profilerights', ['name' => $right]);
        }

        self::removeRightsFromSession();
    }
}

