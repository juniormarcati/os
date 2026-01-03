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

define('PLUGIN_OSFREE_VERSION', '0.3.0');
define('PLUGIN_OSFREE_MIN_GLPI', '10.0.0');
define('PLUGIN_OSFREE_MAX_GLPI', '11.0.99');

include_once __DIR__ . '/hook.php';
include_once __DIR__ . '/inc/config.class.php';
include_once __DIR__ . '/inc/profile.class.php';
include_once __DIR__ . '/inc/entity.class.php';
include_once __DIR__ . '/inc/rn.class.php';

if (file_exists(__DIR__ . '/inc/colors.class.php')) {
    include_once __DIR__ . '/inc/colors.class.php';
}

function plugin_init_osfree()
{
    global $PLUGIN_HOOKS;
    $PLUGIN_HOOKS['csrf_compliant']['osfree'] = true;

    if (!Session::getLoginUserID()) {
        return true;
    }

    $domain = 'osfree';
    $locale_dir = Plugin::getPhpDir('osfree') . '/locales';

    if (method_exists('Plugin', 'loadLang')) {
        Plugin::loadLang($domain);
    }

    Plugin::registerClass('PluginOsfreeConfig', [
        'addtabon' => 'Ticket'
    ]);
    Plugin::registerClass('PluginOsfreeProfile', [
        'addtabon' => ['Profile']
    ]);
    Plugin::registerClass('PluginOsfreeEntity', [
        'addtabon' => ['Entity']
    ]);

    if (class_exists('PluginOsfreeProfile')) {
        PluginOsfreeProfile::initProfile();
    }

    $PLUGIN_HOOKS['item_update']['osfree'] = [
        'Profile' => 'plugin_osfree_item_update_profile'
    ];

    if (Session::haveRight('plugin_osfree', READ)) {
        $PLUGIN_HOOKS['menu_toadd']['osfree'] = ['plugins' => 'PluginOsfreeConfig'];
    }

    if (Session::haveRight('plugin_osfree', READ) || Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page']['osfree'] = 'front/index.php';
    }

    $PLUGIN_HOOKS['change_profile']['osfree'] = 'plugin_change_profile_osfree';

    return true;
}

function plugin_change_profile_osfree()
{
    if (isset($_SESSION["glpi_plugin_osfree_profile"])) {
        unset($_SESSION["glpi_plugin_osfree_profile"]);
    }

    $plugin_osfree_right = $_SESSION['glpiactiveprofile']['plugin_osfree'] ?? 0;

    if ($plugin_osfree_right > 0) {
        $_SESSION["glpi_plugin_osfree_profile"] = [];

        $RIGHT_TICKET_TAB = 1;
        $RIGHT_ENTITY_TAB = 2;
        $RIGHT_PROFILE_TAB = 4;

        if ($plugin_osfree_right & $RIGHT_TICKET_TAB) {
            $_SESSION["glpi_plugin_osfree_profile"]['ticket_tab'] = 'r';
        }

        if ($plugin_osfree_right & $RIGHT_ENTITY_TAB) {
            $_SESSION["glpi_plugin_osfree_profile"]['entity_tab'] = 'r';
        }

        if ($plugin_osfree_right & $RIGHT_PROFILE_TAB) {
            $_SESSION["glpi_plugin_osfree_profile"]['profile_tab'] = 'w';
        }

        $_SESSION["glpi_plugin_osfree_profile"]['osfree'] = ($plugin_osfree_right > 0) ? 'r' : 'n';

        if ($plugin_osfree_right >= UPDATE) {
            $_SESSION["glpi_plugin_osfree_profile"]['osfree'] = 'w';
        }
    }

    if (class_exists('PluginOsfreeProfile') && method_exists('PluginOsfreeProfile', 'initProfile')) {
        try {
            PluginOsfreeProfile::initProfile();
        } catch (Exception $e) {
        }
    }

    if (isset($_SESSION['glpi_tabs'])) {
        unset($_SESSION['glpi_tabs']);
    }
}

function plugin_version_osfree()
{
    global $CFG_GLPI, $TRANSLATE;

    $lang = '';
    if (class_exists('Session') && method_exists('Session', 'getLanguage')) {
        $lang = Session::getLanguage() ?? '';
    }
    if (!$lang && isset($CFG_GLPI['language'])) {
        $lang = $CFG_GLPI['language'];
    }

    if (
        method_exists('Plugin', 'loadLang')
        && isset($TRANSLATE)
        && is_object($TRANSLATE)
    ) {
        Plugin::loadLang('osfree', $lang);
    }

    return [
        'name' => __('Work Order Free', 'osfree'),
        'version' => PLUGIN_OSFREE_VERSION,
        'author' => '<a href="https://github.com/juniormarcati">Marcati</a>',
        'license' => 'AGPLv3+',
        'homepage' => 'https://github.com/juniormarcati/os',
        'readme' => 'https://github.com/juniormarcati/os/blob/master/Readme.md',
        'issues' => 'https://github.com/juniormarcati/os/issues',
        'requirements' => [
            'glpi' => [
                  'min' => PLUGIN_OSFREE_MIN_GLPI,
                  'max' => PLUGIN_OSFREE_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.1'
            ]
        ]
    ];
}

function plugin_check_prerequisites_osfree()
{
    if (
          version_compare(GLPI_VERSION, PLUGIN_OSFREE_MIN_GLPI, 'lt') ||
          version_compare(GLPI_VERSION, PLUGIN_OSFREE_MAX_GLPI, 'gt')
    ) {
        echo sprintf(
            __('Plugin OS requires GLPI version %s to %s.', 'osfree'),
            PLUGIN_OSFREE_MIN_GLPI,
            PLUGIN_OSFREE_MAX_GLPI
        );
        return false;
    }

      if (version_compare(PHP_VERSION, '8.1', 'lt')) {
          echo __('Plugin OS requires PHP version 8.1 or higher.', 'osfree');
        return false;
    }

    $required_extensions = ['json', 'curl'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
              echo sprintf(__('Plugin OS requires PHP extension: %s', 'osfree'), $ext);
            return false;
        }
    }

    return true;
}

function plugin_check_config_osfree()
{
    return true;
}

function plugin_install_osfree()
{
    return plugin_osfree_install();
}

function plugin_uninstall_osfree()
{
    return plugin_osfree_uninstall();
}
