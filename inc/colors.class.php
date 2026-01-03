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

class PluginOsfreeColors extends CommonDBTM
{
    static $rightname = 'config';

    static function getTable($classname = null) {
        return 'glpi_plugin_os_colors';
    }

    static function getTypeName($nb = 0) {
        return _n('WO Color', 'WO Colors', $nb, 'osfree');
    }

    static function getColors() {
        global $DB;

        try {
            $iterator = $DB->request([
                'SELECT' => [
                    'primary_color',
                    'secondary_color',
                    'accent_color',
                    'light_color'
                ],
                'FROM'   => self::getTable(),
                'LIMIT'  => 1
            ]);

            if (count($iterator) > 0) {
                $colors = $iterator->current();
                return [
                    'primary' => Html::cleanInputText($colors['primary_color'] ?? '#2563EB'),
                    'secondary' => Html::cleanInputText($colors['secondary_color'] ?? '#0F172A'),
                    'accent' => Html::cleanInputText($colors['accent_color'] ?? '#F59E0B'),
                    'light' => Html::cleanInputText($colors['light_color'] ?? '#F8FAFC')
                ];
            }
        } catch (Exception $e) {
        }

        return [
            'primary' => '#2563EB',
            'secondary' => '#0F172A',
            'accent' => '#F59E0B',
            'light' => '#F8FAFC'
        ];
    }
}

