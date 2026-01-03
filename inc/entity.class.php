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

class PluginOsfreeEntity extends CommonDBTM
{
    static $rightname = 'plugin_osfree';
    static protected $notable = false;
    public static $itemtype = 'PluginOsfreeEntity';

    public static function normalizeCompanyName(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = trim(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($normalized === '') {
            return '';
        }

        // Remove HTML tags but keep textual content
        $normalized = strip_tags($normalized);

        // Remove control characters that could break rendering
        $normalized = preg_replace('/[\x00-\x1F\x7F]/u', '', $normalized);
        if ($normalized === null) {
            return '';
        }

        return $normalized;
    }

    static function getTable($classname = null)
    {
        return 'glpi_plugin_os_rn';
    }

    static function getTypeName($nb = 0)
    {
        return _n('Entity WO Configuration', 'Entity WO Configurations', $nb, 'osfree');
    }

    static function getDocumentConfig()
    {
        $lang = $_SESSION['glpilanguage'] ?? 'en_US';

        switch ($lang) {
            case 'pt_BR':
                return [
                    'type' => 'cnpj',
                    'label' => __('CNPJ', 'osfree'),
                    'placeholder' => '00.000.000/0000-00',
                    'help' => __('14 digits for CNPJ, or leave empty', 'osfree'),
                    'format_regex' => '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
                    'format_cnpj' => '$1.$2.$3/$4-$5',
                    'validation_lengths' => [14]
                ];
            case 'en_US':
            default:
                return [
                    'type' => 'ein',
                    'label' => __('EIN (Employer Identification Number)', 'osfree'),
                    'placeholder' => '00-0000000',
                    'help' => __('9 digits for EIN (XX-XXXXXXX format), or leave empty', 'osfree'),
                    'format_regex' => '/(\d{2})(\d{7})/',
                    'format_ein' => '$1-$2',
                    'validation_lengths' => [9]
                ];
        }
    }

    static function formatDocument($document)
    {
        if (empty($document)) return '';

        $config = self::getDocumentConfig();
        $document = preg_replace('/[^0-9]/', '', $document);

        switch ($config['type']) {
            case 'cnpj':
                if (strlen($document) === 14) {
                    return preg_replace($config['format_regex'], $config['format_cnpj'], $document);
                }
                break;
            case 'ein':
                if (strlen($document) === 9) {
                    return preg_replace($config['format_regex'], $config['format_ein'], $document);
                }
                break;
        }

        return $document;
    }

    function canCreateItem(): bool
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    function canViewItem(): bool
    {
        return Session::haveRight(static::$rightname, READ);
    }

    function canUpdateItem(): bool
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    function canDeleteItem(): bool
    {
        return Session::haveRight(static::$rightname, DELETE);
    }

    function prepareInputForAdd($input)
    {
        return $this->prepareInput($input);
    }

    function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    private function prepareInput($input)
    {

        if (isset($input['entities_id'])) {
            $input['entities_id'] = (int)$input['entities_id'];

            if ($input['entities_id'] < 0) {
                Session::addMessageAfterRedirect(
                    __('Invalid entity ID', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }
        }

        if (isset($input['rn'])) {
            $rn = preg_replace('/[^0-9]/', '', $input['rn']);
            $config = self::getDocumentConfig();

            if (strlen($rn) > 0 && !in_array(strlen($rn), $config['validation_lengths'])) {
                $errorMsg = '';
                switch ($config['type']) {
                    case 'cnpj':
                        $errorMsg = __('Invalid CNPJ format. Use 14 digits for CNPJ.', 'osfree');
                        break;
                    case 'ein':
                        $errorMsg = __('Invalid EIN format. Use 9 digits (XX-XXXXXXX format).', 'osfree');
                        break;
                }

                Session::addMessageAfterRedirect($errorMsg, false, ERROR);
                return false;
            }

            $input['rn'] = $rn; 
        }

        if (isset($input['company_name'])) {
            $companyName = self::normalizeCompanyName($input['company_name']);

            $nameLength = function_exists('mb_strlen') ? mb_strlen($companyName) : strlen($companyName);
            if ($nameLength > 255) {
                Session::addMessageAfterRedirect(
                    __('Company name is too long (maximum 255 characters)', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }

            $input['company_name'] = $companyName;
        }

        $current_time = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');
        if (!isset($input['id']) || empty($input['id'])) {
            $input['date_creation'] = $current_time;
        }
        $input['date_mod'] = $current_time;

        return $input;
    }

    function post_addItem()
    {
        Log::history(
            $this->fields['id'],
            __CLASS__,
            [0, '', sprintf(
                __('Entity WO configuration created for entity %d by %s', 'osfree'),
                $this->fields['entities_id'],
                $_SESSION['glpiname'] ?? 'Sistema'
            )]
        );
    }

    function post_updateItem($history = 1)
    {
        if ($history) {
            Log::history(
                $this->fields['id'],
                __CLASS__,
                [0, '', sprintf(
                    __('Entity WO configuration updated for entity %d by %s', 'osfree'),
                    $this->fields['entities_id'],
                    $_SESSION['glpiname'] ?? 'Sistema'
                )]
            );
        }
    }

    static function getEntityConfig($entities_id)
    {
        global $DB;

        $entities_id = (int)$entities_id;

        if ($entities_id === 0) {
            if (!Session::haveRight('plugin_osfree', READ) || !Session::haveRight('config', READ)) {
                return null;
            }
        } else {
            if (!Session::haveAccessToEntity($entities_id)) {
                return null;
            }
        }

        try {
            $iterator = $DB->request([
                'SELECT' => ['id', 'entities_id', 'rn', 'company_name', 'date_creation', 'date_mod'],
                'FROM'   => self::getTable(),
                'WHERE'  => ['entities_id' => $entities_id],
                'LIMIT'  => 1
            ]);

            if (count($iterator) > 0) {
                $config = $iterator->current();

                return [
                    'id' => (int)$config['id'],
                    'entities_id' => (int)$config['entities_id'],
                    'rn' => Html::cleanInputText($config['rn'] ?? ''),
                    'company_name' => self::normalizeCompanyName($config['company_name'] ?? ''),
                    'date_creation' => $config['date_creation'] ?? null,
                    'date_mod' => $config['date_mod'] ?? null
                ];
            } else {

            }
        } catch (Exception $e) {
            
        }

        return [
            'id' => 0,
            'entities_id' => $entities_id,
            'rn' => '',
            'company_name' => '',
            'date_creation' => null,
            'date_mod' => null
        ];
    }

    static function saveEntityConfig($data)
    {
        
        if (!Session::haveRight('plugin_osfree', UPDATE)) {
            Session::addMessageAfterRedirect(
                __('No permission to change settings', 'osfree'),
                false,
                ERROR
            );
            return false;
        }

        try {
            global $DB;

            if (!$DB->tableExists(self::getTable())) {
                throw new Exception('Database table ' . self::getTable() . ' does not exist');
            }

            $entities_id = (int)$data['entities_id'];
            $rn = isset($data['rn']) ? $data['rn'] : '';
            $company_name = self::normalizeCompanyName($data['company_name'] ?? '');
            $nameLength = function_exists('mb_strlen') ? mb_strlen($company_name) : strlen($company_name);
            if ($nameLength > 255) {
                Session::addMessageAfterRedirect(
                    __('Company name is too long (maximum 255 characters)', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }

            $current_time = $_SESSION['glpi_currenttime'] ?? date('Y-m-d H:i:s');

            $iterator = $DB->request([
                'SELECT' => ['id'],
                'FROM' => self::getTable(),
                'WHERE' => ['entities_id' => $entities_id]
            ]);

            $recordExists = false;
            $existingId = null;
            foreach ($iterator as $row) {
                $recordExists = true;
                $existingId = $row['id'];
                break;
            }

            $dataFields = [
                'rn' => $rn,
                'company_name' => $company_name,
                'date_mod' => $current_time
            ];

            if ($recordExists) {
                
                $updateResult = $DB->update(
                    self::getTable(),
                    $dataFields,
                    ['id' => $existingId]
                );

                if (!$updateResult) {
                    throw new Exception('Erro ao atualizar registro via ORM');
                }

                Session::addMessageAfterRedirect(
                    __('Entity configuration updated successfully', 'osfree'),
                    false,
                    INFO
                );
                return true;
            } else {
                
                $dataFields['entities_id'] = $entities_id;
                $dataFields['date_creation'] = $current_time;
                
                $insertResult = $DB->insert(self::getTable(), $dataFields);

                if (!$insertResult) {
                    throw new Exception('Erro ao inserir registro via ORM');
                }

                $newId = $insertResult;

                Session::addMessageAfterRedirect(
                    __('Entity configuration created successfully', 'osfree'),
                    false,
                    INFO
                );
                return $newId;
            }
        } catch (Exception $e) {

            Session::addMessageAfterRedirect(
                __('Error saving entity configuration', 'osfree') . ': ' . $e->getMessage(),
                false,
                ERROR
            );

            return false;
        }
    }

    static function deleteEntityConfig($entities_id)
    {
        if (!Session::haveRight('plugin_osfree', DELETE)) {
            Session::addMessageAfterRedirect(
                __('No permission to delete settings', 'osfree'),
                false,
                ERROR
            );
            return false;
        }

        try {
            global $DB;

            $entities_id = (int)$entities_id;

            $iterator = $DB->request([
                'SELECT' => ['id'],
                'FROM' => self::getTable(),
                'WHERE' => ['entities_id' => $entities_id]
            ]);

            $recordExists = false;
            $existingId = null;
            foreach ($iterator as $row) {
                $recordExists = true;
                $existingId = $row['id'];
                break;
            }

            if ($recordExists) {

                $deleteResult = $DB->delete(
                    self::getTable(),
                    ['id' => $existingId]
                );

                if (!$deleteResult) {
                    throw new Exception('Erro ao deletar registro via ORM');
                }

                Session::addMessageAfterRedirect(
                    __('Entity configuration deleted successfully', 'osfree'),
                    false,
                    INFO
                );
                return true;
            } else {
                Session::addMessageAfterRedirect(
                    __('No configuration found to delete', 'osfree'),
                    false,
                    WARNING
                );
                return false;
            }
        } catch (Exception $e) {

            Session::addMessageAfterRedirect(
                __('Error deleting entity configuration', 'osfree'),
                false,
                ERROR
            );

            return false;
        }
    }

    static function formatRN($rn)
    {
        return self::formatDocument($rn);
    }

    function showEntityForm($entities_id)
    {
        if (!$this->canViewItem()) {
            Html::displayRightError();
            return false;
        }

        if ($entities_id === 0) {
            if (!Session::haveRight('plugin_osfree', READ) || !Session::haveRight('config', READ)) {
                echo "<div class='center'>";
                echo "<div class='alert alert-danger'>";
                echo "<i class='fas fa-ban'></i> ";
                echo __('No permission to access root entity', 'osfree');
                echo "</div>";
                echo "</div>";
                return false;
            }
        } else {
            if (!Session::haveAccessToEntity($entities_id)) {
                echo "<div class='center'>";
                echo "<div class='alert alert-danger'>";
                echo "<i class='fas fa-ban'></i> ";
                echo __('No permission to access this entity', 'osfree');
                echo "</div>";
                echo "</div>";
                return false;
            }
        }

        $canedit = $this->canUpdateItem();
        $candelete = $this->canDeleteItem();
        $config = self::getEntityConfig($entities_id);
        $docConfig = self::getDocumentConfig();

        echo "<div class='center' style='width: 950px; margin: 0 auto;'>";

        if ($canedit) {
            echo "<form method='post' action='" . Plugin::getWebDir('osfree') . "/front/entity.form.php' name='entity_rn_form'>";
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('entities_id', ['value' => $entities_id]);
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Entity WO Settings', 'osfree') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='30%'>";
        echo "<label for='company_name'>" . __('Company Name', 'osfree') . "</label>";
        echo "<br><small style='color: #666;'>" . __('Full company or organization name', 'osfree') . "</small>";
        echo "</td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('company_name', [
                'value' => $config['company_name'],
                'size' => 50,
                'maxlength' => 255,
                'placeholder' => __('Enter company name', 'osfree'),
                'id' => 'company_name_input'
            ]);
        } else {
            echo Html::entities_deep($config['company_name']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='30%'>";
        echo "<label for='rn'>" . $docConfig['label'] . "</label>";
        echo "<br><small style='color: #666;'>" . $docConfig['help'] . "</small>";
        echo "</td>";
        echo "<td>";
        if ($canedit) {
            $current_value = self::formatDocument($config['rn']);

            echo Html::input('rn', [
                'value' => $current_value,
                'size' => 20,
                'maxlength' => 18,
                'placeholder' => $docConfig['placeholder'],
                'id' => 'rn_input',
                'data-doc-type' => $docConfig['type']
            ]);
        } else {
            echo self::formatDocument($config['rn']);
        }
        echo "</td>";
        echo "</tr>";

        if ($config['date_creation']) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Created', 'osfree') . "</td>";
            echo "<td>" . Html::convDateTime($config['date_creation']) . "</td>";
            echo "</tr>";
        }

        if ($config['date_mod']) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Last update', 'osfree') . "</td>";
            echo "<td>" . Html::convDateTime($config['date_mod']) . "</td>";
            echo "</tr>";
        }

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Status', 'osfree') . "</td>";
        echo "<td>";
        if ($config['id'] > 0) {
            echo "<span style='color: green;'><i class='fas fa-check-circle'></i> " . __('Configured', 'osfree') . "</span>";
        } else {
            echo "<span style='color: orange;'><i class='fas fa-exclamation-triangle'></i> " . __('Not configured', 'osfree') . "</span>";
        }
        echo "</td>";
        echo "</tr>";

        if ($canedit || $candelete) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2' class='center'>";

            if ($canedit) {
                echo Html::submit(_sx('button', 'Save'), [
                    'name' => 'update',
                    'class' => 'btn btn-primary'
                ]);
                echo "&nbsp;";
            }

            if ($candelete && $config['id'] > 0) {
                echo Html::submit(_sx('button', 'Delete'), [
                    'name' => 'delete',
                    'class' => 'btn btn-danger',
                    'onclick' => 'return confirm("' . __('Are you sure you want to delete this configuration?', 'osfree') . '");'
                ]);
            }

            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

        if ($canedit) {
            Html::closeForm();
        }

        echo "</div>";

        if ($canedit) {
            echo "<script type='text/javascript'>
            $(document).ready(function() {
                const docType = $('#rn_input').data('doc-type');
                
                $('#rn_input').on('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    
                    if (docType === 'cnpj') {
                        if (value.length <= 14) {
                            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                            value = value.replace(/(\d{4})(\d)/, '$1-$2');
                        }
                    } else if (docType === 'ein') {
                        if (value.length <= 9) {
                            value = value.replace(/^(\d{2})(\d)/, '$1-$2');
                        }
                    }
                    
                    e.target.value = value;
                });
            });
            </script>";
        }

        return true;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Entity') {
            if (class_exists('PluginOsfreeProfile') && !PluginOsfreeProfile::canAccessEntityTab()) {
                return '';
            }

            if (Session::haveRight('plugin_osfree', READ)) {
                return __('Work Order', 'osfree');
            }
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if (!Session::haveRight('plugin_osfree', READ)) {
            echo "<div class='center'>";
            echo "<p>" . __('Access denied') . "</p>";
            echo "</div>";
            return false;
        }

        switch (get_class($item)) {
            case 'Entity':
                $entity = new self();
                $entity->showEntityForm($item->getID());
                break;
        }
        return true;
    }
}
