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

class PluginOsfreeRn extends CommonDBTM {
    
    static $rightname = 'plugin_osfree';
    
    public static function getCompanyNameByEntity($entities_id) {
        global $DB;
        
        $iterator = $DB->request([
            'SELECT' => ['company_name'],
            'FROM' => 'glpi_plugin_os_rn',
            'WHERE' => ['entities_id' => (int)$entities_id],
            'LIMIT' => 1
        ]);
        
        if (count($iterator) > 0) {
            $row = $iterator->current();
            return PluginOsfreeEntity::normalizeCompanyName($row['company_name']);
        }
        
        return '';
    }
    
    static function getTable($classname = null) {
        return 'glpi_plugin_os_rn';
    }
    
    static function getTypeName($nb = 0) {
        return _n('RN Configuration', 'RN Configurations', $nb, 'osfree');
    }
}
