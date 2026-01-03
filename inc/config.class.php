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
class PluginOsfreeConfig extends CommonDBTM
{
    static $rightname = 'config';
    static protected $notable = false;
    public static $itemtype = 'PluginOsfreeConfig';

    static function getTable($classname = null)
    {
        return 'glpi_plugin_os_config';
    }

    static function getTypeName($nb = 0)
    {
        return _n('WO Configuration', 'WO Configurations', $nb, 'osfree');
    }

    static function getMenuName()
    {
        return __('Work Order', 'osfree');
    }

    static function getMenuContent()
    {
        global $CFG_GLPI;
        $menu = [];
        $menu['title'] = __('Work Order', 'osfree');
        $menu['page'] = "/plugins/osfree/front/index.php";
        $menu['icon'] = "fas fa-paperclip";
        return $menu;
    }

    public function canCreateItem(): bool
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    public function canViewItem(): bool
    {
        return Session::haveRight(static::$rightname, READ);
    }

    public function canUpdateItem(): bool
    {
        return Session::haveRight(static::$rightname, UPDATE);
    }

    public function canDeleteItem(): bool
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
        if (isset($input['name']) || isset($input['name_form'])) {
            $name = $input['name'] ?? $input['name_form'] ?? '';
            $input['name'] = Html::cleanInputText($name);

            if (strlen($input['name']) < 2) {
                Session::addMessageAfterRedirect(
                    __('Company name must be at least 2 characters long', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }
        }

        if (isset($input['cnpj']) || isset($input['cnpj_form'])) {
            $cnpj = $input['cnpj'] ?? $input['cnpj_form'] ?? '';
            $input['cnpj'] = preg_replace('/[^0-9]/', '', $cnpj);

            if (!empty($input['cnpj']) && strlen($input['cnpj']) !== 14) {
                Session::addMessageAfterRedirect(
                    __('Incorrect record format', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }
        }

        if (isset($input['address']) || isset($input['address_form'])) {
            $address = $input['address'] ?? $input['address_form'] ?? '';
            $input['address'] = Html::cleanInputText($address);
        }

        if (isset($input['phone']) || isset($input['phone_form'])) {
            $phone = $input['phone'] ?? $input['phone_form'] ?? '';
            $input['phone'] = Html::cleanInputText($phone);
        }

        if (isset($input['city']) || isset($input['city_form'])) {
            $city = $input['city'] ?? $input['city_form'] ?? '';
            $input['city'] = Html::cleanInputText($city);
        }

        if (isset($input['site']) || isset($input['site_form'])) {
            $site = $input['site'] ?? $input['site_form'] ?? '';
            $input['site'] = trim($site);

            if (!empty($input['site']) && !filter_var($input['site'], FILTER_VALIDATE_URL)) {
                if (!preg_match('/^https?:\/\//', $input['site'])) {
                    $input['site'] = 'http://' . $input['site'];
                    if (!filter_var($input['site'], FILTER_VALIDATE_URL)) {
                        Session::addMessageAfterRedirect(
                            __('Invalid website URL', 'osfree'),
                            false,
                            ERROR
                        );
                        return false;
                    }
                }
            }
        }

        if (isset($input['currency']) || isset($input['currency_form'])) {
            $currency = $input['currency'] ?? $input['currency_form'] ?? 'BRL';

            $allowedCurrencies = ['BRL', 'USD', 'EUR', 'GBP', 'ARS', 'CLP', 'COP', 'PEN', 'UYU'];

            if (!in_array($currency, $allowedCurrencies)) {
                Session::addMessageAfterRedirect(
                    __('Invalid currency selected', 'osfree'),
                    false,
                    ERROR
                );
                return false;
            }

            $input['currency'] = $currency;
        }

        if (isset($input['label_width']) || isset($input['label_width_form'])) {
            $labelWidth = $input['label_width'] ?? $input['label_width_form'] ?? 60;
            $input['label_width'] = (int)$labelWidth;

            if (!in_array($input['label_width'], [50, 60, 70, 80, 90, 100])) {
                $input['label_width'] = 60; 
            }
        }

        if (isset($input['label_custom_text']) || isset($input['label_custom_text_form'])) {
            $customText = $input['label_custom_text'] ?? $input['label_custom_text_form'] ?? '';
            $input['label_custom_text'] = Html::cleanInputText($customText);
        }

        $formFields = ['name_form', 'cnpj_form', 'address_form', 'phone_form', 'city_form', 'site_form', 'currency_form', 'label_width_form', 'label_custom_text_form'];
        foreach ($formFields as $field) {
            unset($input[$field]);
        }

        return $input;
    }

    function post_addItem()
    {
        Log::history(
            $this->fields['id'],
            __CLASS__,
            [0, '', sprintf(__('WO configuration created by %s', 'osfree'), $_SESSION['glpiname'] ?? 'Sistema')]
        );

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    function post_updateItem($history = 1)
    {
        if ($history) {
            Log::history(
                $this->fields['id'],
                __CLASS__,
                [0, '', sprintf(__('WO configuration changed by %s', 'osfree'), $_SESSION['glpiname'] ?? 'Sistema')]
            );
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    static function getConfig()
    {
        global $DB;

        try {
            $iterator = $DB->request([
                'SELECT' => [
                    'id',
                    'name',
                    'cnpj',
                    'address',
                    'phone',
                    'city',
                    'site',
                    'currency',
                    'label_width',
                    'label_custom_text'
                ],
                'FROM'   => self::getTable(),
                'WHERE'  => ['id' => 1],
                'LIMIT'  => 1
            ]);

            if (count($iterator) > 0) {
                $config = $iterator->current();

                return [
                    'id' => (int)$config['id'],
                    'name' => Html::cleanInputText($config['name'] ?? ''),
                    'cnpj' => Html::cleanInputText($config['cnpj'] ?? ''),
                    'address' => Html::cleanInputText($config['address'] ?? ''),
                    'phone' => Html::cleanInputText($config['phone'] ?? ''),
                    'city' => Html::cleanInputText($config['city'] ?? ''),
                    'site' => Html::cleanInputText($config['site'] ?? ''),
                    'currency' => Html::cleanInputText($config['currency'] ?? 'BRL'),
                    'label_width' => (int)($config['label_width'] ?? 80),
                    'label_custom_text' => Html::cleanInputText($config['label_custom_text'] ?? '')
                ];
            }
        } catch (Exception $e) {
        }

        return [
            'id' => 1,
            'name' => __('Your Company', 'osfree'),
            'cnpj' => '',
            'address' => '',
            'phone' => '',
            'city' => '',
            'site' => '',
            'currency' => 'BRL',
            'label_width' => 80,
            'label_custom_text' => ''
        ];
    }

    static function saveConfig($data)
    {
        if (!Session::haveRight('config', UPDATE)) {
            Session::addMessageAfterRedirect(
                __('No permission to change settings', 'osfree'),
                false,
                ERROR
            );
            return false;
        }

        $config = new self();

        try {
            global $DB;

            $iterator = $DB->request([
                'SELECT' => ['id'],
                'FROM'   => self::getTable(),
                'WHERE'  => ['id' => 1],
                'LIMIT'  => 1
            ]);

            $data['id'] = 1; 

            if (count($iterator) > 0) {
                $result = $config->update($data);
                if ($result) {
                    Session::addMessageAfterRedirect(
                        __('Configuration updated successfully', 'osfree'),
                        false,
                        INFO
                    );
                }
            } else {
                $result = $config->add($data);
                if ($result) {
                    Session::addMessageAfterRedirect(
                        __('Configuration created successfully', 'osfree'),
                        false,
                        INFO
                    );
                }
            }

            if (!$result) {
                Session::addMessageAfterRedirect(
                    __('Error saving configuration', 'osfree'),
                    false,
                    ERROR
                );
            }

            return $result;
        } catch (Exception $e) {

            Session::addMessageAfterRedirect(
                __('Internal system error', 'osfree'),
                false,
                ERROR
            );

            return false;
        }
    }

    function showConfigForm()
    {
        if (!$this->canViewItem()) {
            Html::displayRightError();
            return false;
        }

        $canedit = $this->canUpdateItem();
        $config = self::getConfig();

        if ($canedit) {
            echo "<div class='center' style='width: 950px; margin: 0 auto;'>";
            echo "<form method='post' action='" . Plugin::getWebDir('osfree') . "/front/config.php'>";
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('Company Settings', 'osfree') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><label for='name_form'>" . __('Company Name', 'osfree') . " <span class='red'>*</span></label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('name_form', [
                'value' => $config['name'],
                'size' => 40,
                'required' => true
            ]);
        } else {
            echo Html::cleanInputText($config['name']);
        }
        echo "</td>";

        echo "<td><label for='cnpj_form'>" . __('EIN', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('cnpj_form', [
                'value' => $config['cnpj'],
                'size' => 20,
                'maxlength' => 18,
                'placeholder' => '00.000.000/0000-00'
            ]);
        } else {
            echo Html::cleanInputText($config['cnpj']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><label for='address_form'>" . __('Address', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('address_form', [
                'value' => $config['address'],
                'size' => 40
            ]);
        } else {
            echo Html::cleanInputText($config['address']);
        }
        echo "</td>";

        echo "<td><label for='phone_form'>" . __('Phone', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('phone_form', [
                'value' => $config['phone'],
                'size' => 20
            ]);
        } else {
            echo Html::cleanInputText($config['phone']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><label for='city_form'>" . __('City', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('city_form', [
                'value' => $config['city'],
                'size' => 30
            ]);
        } else {
            echo Html::cleanInputText($config['city']);
        }
        echo "</td>";

        echo "<td><label for='site_form'>" . __('Website', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('site_form', [
                'type' => 'url',
                'value' => $config['site'],
                'size' => 40,
                'placeholder' => 'https://exemplo.com'
            ]);
        } else {
            if (!empty($config['site'])) {
                echo "<a href='" . Html::cleanInputText($config['site']) . "' target='_blank'>" . Html::cleanInputText($config['site']) . "</a>";
            }
        }
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td><label for='currency_form'>" . __('Currency', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            $currencies = [
                'BRL' => __('Brazilian Real (BRL)', 'osfree'),
                'USD' => __('US Dollar (USD)', 'osfree'),
                'EUR' => __('Euro (EUR)', 'osfree'),
                'GBP' => __('British Pound (GBP)', 'osfree'),
                'ARS' => __('Argentine Peso (ARS)', 'osfree'),
                'CLP' => __('Chilean Peso (CLP)', 'osfree'),
                'COP' => __('Colombian Peso (COP)', 'osfree'),
                'PEN' => __('Peruvian Sol (PEN)', 'osfree'),
                'UYU' => __('Uruguayan Peso (UYU)', 'osfree')
            ];

            Dropdown::showFromArray('currency_form', $currencies, [
                'value' => $config['currency'] ?? 'BRL',
                'display_emptychoice' => false
            ]);
        } else {
            $currencies = [
                'BRL' => __('Brazilian Real (BRL)', 'osfree'),
                'USD' => __('US Dollar (USD)', 'osfree'),
                'EUR' => __('Euro (EUR)', 'osfree'),
                'GBP' => __('British Pound (GBP)', 'osfree'),
                'ARS' => __('Argentine Peso (ARS)', 'osfree'),
                'CLP' => __('Chilean Peso (CLP)', 'osfree'),
                'COP' => __('Colombian Peso (COP)', 'osfree'),
                'PEN' => __('Peruvian Sol (PEN)', 'osfree'),
                'UYU' => __('Uruguayan Peso (UYU)', 'osfree')
            ];
            echo $currencies[$config['currency']] ?? $config['currency'];
        }
        echo "</td>";

        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='4'>" . __('Label settings', 'osfree') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td><label for='label_width_form'>" . __('Label Width (mm)', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            $widthOptions = [
                50 => '50mm',
                60 => '60mm',
                70 => '70mm',
                80 => '80mm',
                90 => '90mm',
                100 => '100mm'
            ];
            Dropdown::showFromArray('label_width_form', $widthOptions, [
                'value' => $config['label_width'],
                'display_emptychoice' => false
            ]);
        } else {
            echo $config['label_width'] . 'mm';
        }
        echo "</td>";

        echo "<td><label for='label_custom_text_form'>" . __('Custom Text', 'osfree') . "</label></td>";
        echo "<td>";
        if ($canedit) {
            echo Html::input('label_custom_text_form', [
                'value' => $config['label_custom_text'],
                'size' => 40
            ]);
        } else {
            echo Html::cleanInputText($config['label_custom_text']);
        }
        echo "</td>";
        echo "</tr>";

        if ($canedit) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='4' class='center'>";
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

        if ($canedit) {
            Html::closeForm();
            echo "</div>";

            echo "<script type='text/javascript'>
            $(document).ready(function() {
                $('input[name=\"cnpj_form\"]').on('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 14) {
                        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                        value = value.replace(/(\d{4})(\d)/, '$1-$2');
                        e.target.value = value;
                    }
                });
            });
            </script>";
        }

        return true;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Ticket') {
            if (class_exists('PluginOsfreeProfile') && !PluginOsfreeProfile::canAccessTicketTab()) {
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
        if (!Session::haveRight('plugin_osfree', READ)) {
            echo "<div class='center'>";
            echo "<p>" . __('Access denied') . "</p>";
            echo "</div>";
            return false;
        }

        switch (get_class($item)) {
            case 'Ticket':
                $config = new self();
                $config->showFormDisplay();
                break;
        }
        return true;
    }

    function showFormDisplay()
    {
        global $CFG_GLPI, $DB;
        $ID = $_REQUEST['id'];
        $url = $CFG_GLPI['url_base'];


        $urlA4 = "{$url}/plugins/osfree/front/os_pdf.php?id={$ID}";
        $urlLabel = "{$url}/plugins/osfree/front/os_pdflabel.php?id={$ID}";

        echo "<style>
      .os-container {
          font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
          padding: 0;
          background: #fff;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.08);
          max-width: 100%;
          margin: 0 auto;
          overflow: hidden;
      }

      .os-premium-banner {
          position: relative;
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 18px;
          padding: 14px 18px;
          background: linear-gradient(135deg, #00BAC4, #23A455);
          color: #ffffff;
          box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
      }

      .os-premium-banner::before {
          content: '';
          position: absolute;
          inset: 0;
          background: radial-gradient(circle at top right, rgba(255,255,255,0.28), transparent 55%);
          opacity: 0.9;
          pointer-events: none;
      }

      .os-premium-content {
          position: relative;
          display: flex;
          flex-direction: column;
          gap: 6px;
      }

      .os-premium-eyebrow {
          font-size: 0.75em;
          letter-spacing: 0.1em;
          text-transform: uppercase;
          opacity: 0.8;
      }

      .os-premium-title {
          font-size: 0.98em;
          font-weight: 600;
      }

      .os-premium-desc {
          font-size: 0.8em;
          opacity: 0.9;
          max-width: 460px;
          margin-bottom: 8px;
      }

      .os-premium-footnote {
          font-size: 0.75em;
          opacity: 0.75;
          margin-top: 4px;
      }

      .os-premium-features {
          display: flex;
          flex-wrap: wrap;
          gap: 8px;
      }

      .os-premium-feature {
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 6px 10px;
          background: rgba(255,255,255,0.14);
          border-radius: 999px;
          font-size: 0.78em;
          letter-spacing: 0.01em;
          transition: background 0.2s ease, transform 0.2s ease;
      }

      .os-premium-feature i {
          font-size: 0.9em;
      }

      .os-premium-feature:hover {
          background: rgba(255,255,255,0.24);
          transform: translateY(-1px);
      }

      .os-premium-action {
          position: relative;
          display: inline-flex;
          align-items: center;
          gap: 10px;
          padding: 10px 20px;
          background: rgba(255,255,255,0.14);
          color: #ffffff;
          text-decoration: none;
          border-radius: 999px;
          border: 1px solid rgba(255,255,255,0.22);
          font-weight: 600;
          font-size: 0.9em;
          box-shadow: 0 12px 24px rgba(0,0,0,0.18);
          transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
      }

      .os-premium-action i {
          font-size: 0.95em;
      }

      .os-premium-action:hover {
          transform: translateY(-1px);
          box-shadow: 0 18px 30px rgba(0,0,0,0.22);
          background: rgba(255,255,255,0.22);
      }
      
      .os-toolbar {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 14px 20px;
          background: #f9f9f9;
          border-bottom: 1px solid #eaeaea;
      }
      
      .os-document-info {
          display: flex;
          align-items: center;
          gap: 12px;
      }
      
      .os-document-icon {
          font-size: 1.5em;
          color: #e74c3c;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(231, 76, 60, 0.08);
          width: 36px;
          height: 36px;
          border-radius: 8px;
          transition: all 0.3s ease;
      }
      
      .os-document-details {
          display: flex;
          flex-direction: column;
          gap: 2px;
      }
      
      .os-document-title {
          font-size: 1.05em;
          font-weight: 600;
          color: #333;
      }
      
      .os-document-subtitle {
          font-size: 0.8em;
          color: #888;
      }
      
      .os-controls {
          display: flex;
          align-items: center;
          gap: 8px;
      }
      
      .os-format-selector {
          display: flex;
          background: white;
          border: 1px solid #e0e0e0;
          border-radius: 6px;
          overflow: hidden;
          box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      }
      
      .os-format-btn {
          padding: 7px 12px;
          background: transparent;
          border: none;
          cursor: pointer;
          font-size: 0.85em;
          color: #666;
          position: relative;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 6px;
          transition: all 0.25s ease;
      }
      
      .os-format-btn:first-child {
          border-right: 1px solid #f0f0f0;
      }
      
      .os-format-btn:hover:not(.disabled) {
          background: #f5f5f5;
          color: #333;
      }
      
      .os-format-btn.active {
          background: #3498db;
          color: white;
      }
      
      .os-format-btn.disabled {
          opacity: 0.5;
          cursor: not-allowed;
          background: #f8f8f8;
          color: #999;
      }
      
      .os-format-btn .os-icon {
          font-size: 1em;
      }
      
      .os-print-btn {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 7px;
          padding: 7px 14px;
          background: #34495e;
          color: white;
          border: none;
          border-radius: 6px;
          font-size: 0.85em;
          font-weight: 500;
          cursor: pointer;
          transition: all 0.25s ease;
          box-shadow: 0 2px 5px rgba(52, 73, 94, 0.15);
      }
      
      .os-print-btn:hover:not(.disabled) {
          background: #2c3e50;
          transform: translateY(-1px);
          box-shadow: 0 4px 8px rgba(52, 73, 94, 0.2);
      }
      
      .os-print-btn:active {
          transform: translateY(0);
      }
      
      .button-text {
          display: inline;
      }
      
      @media screen and (max-width: 768px) {
          .os-premium-banner {
              flex-direction: column;
              align-items: flex-start;
              text-align: left;
          }

          .os-premium-features {
              gap: 6px;
          }

          .os-premium-feature {
              width: 100%;
              justify-content: flex-start;
          }

          .os-premium-action {
              width: 100%;
              justify-content: center;
          }

          .button-text {
              display: none; 
          }
          
          .os-format-btn {
              padding: 7px 10px; 
              min-width: 36px;
              justify-content: center;
          }
          
        .os-print-btn {
              padding: 7px 10px; 
              min-width: 36px;
              justify-content: center;
          }
          
          .os-toolbar {
              padding: 10px 12px;
          }
          
          .os-document-subtitle {
              display: none; 
          }
      }
      
      .os-content {
          position: relative;
          height: 700px;
          background: #f5f5f5;
          display: flex;
          align-items: center;
          justify-content: center;
      }
      
      #pdfViewer {
          position: relative;
          width: calc(100% - 2px);
          height: calc(100% - 2px);
          margin: 1px;
          background: white;
          box-shadow: 0 0 25px rgba(0,0,0,0.05);
          transition: all 0.3s ease;
          overflow: auto;
      }
      
      .os-loading {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(255,255,255,0.9);
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          z-index: 100;
          opacity: 0;
          pointer-events: none;
          transition: opacity 0.3s ease;
      }
      
      .os-loading.active {
          opacity: 1;
          pointer-events: auto;
      }
      
      .os-loading-text {
          margin-top: 15px;
          font-size: 0.85em;
          color: #666;
      }
      
      .os-spinner {
          width: 40px;
          height: 40px;
          border: 3px solid rgba(52, 152, 219, 0.2);
          border-radius: 50%;
          border-top-color: #3498db;
          animation: os-spin 1s ease-in-out infinite;
      }
      
      @keyframes os-spin {
          to { transform: rotate(360deg); }
      }
      
      .pdfViewer .page {
          direction: ltr;
          width: 100%;
          height: auto;
          margin: 1px auto;
          position: relative;
          overflow: visible;
          background-clip: content-box;
          background-color: white;
          box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      }
      
      .pdfViewer .page canvas {
          margin: 0 auto;
          display: block;
      }
      
      .fade-effect {
          opacity: 0;
          transition: opacity 0.3s ease;
      }
      
      .fade-effect.show {
          opacity: 1;
      }
  </style>";

        echo "<div class='os-container'>";

        echo "<div class='os-premium-banner'>";
        echo "<div class='os-premium-content'>";
        echo "<span class='os-premium-eyebrow'>" . __('Premium', 'osfree') . "</span>";
        echo "<div class='os-premium-title'>" . __('Upgrade Your Work Orders', 'osfree') . "</div>";
        echo "<div class='os-premium-desc'>" . __('Experience a smarter workflow with these premium highlights:', 'osfree') . "</div>";
        echo "<div class='os-premium-features'>";
        echo "<span class='os-premium-feature'><i class='fas fa-coins'></i>" . __('Ticket cost insights', 'osfree') . "</span>";
        echo "<span class='os-premium-feature'><i class='fas fa-pen-nib'></i>" . __('Digital signatures', 'osfree') . "</span>";
        echo "<span class='os-premium-feature'><i class='fas fa-cubes'></i>" . __('Detailed ticket items', 'osfree') . "</span>";
        echo "<span class='os-premium-feature'><i class='fas fa-sliders-h'></i>" . __('Advanced interface', 'osfree') . "</span>";
        echo "</div>";
        echo "<div class='os-premium-footnote'>" . __('Gain more accuracy, productivity, and professionalism across every operation.', 'osfree') . "</div>";
        echo "</div>";
        echo "<a class='os-premium-action' href='https://pluginos.marcati.com.br' target='_blank' rel='noopener'>";
        echo "<i class='fas fa-star'></i><span>" . __('Discover Premium', 'osfree') . "</span>";
        echo "</a>";
        echo "</div>";

        echo "<div class='os-toolbar'>";

        echo "<div class='os-document-info'>";
        echo "<div class='os-document-icon'><i class='fas fa-file-pdf'></i></div>";
        echo "<div class='os-document-details'>";
        echo "<div class='os-document-title'>" . __('Work Order', 'osfree') . "</div>";
        echo "<div class='os-document-subtitle'>#{$ID}</div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='os-controls'>";

        echo "<div class='os-format-selector'>";
        echo "<button type='button' id='a4Button' class='os-format-btn active' onclick='changeFormat(\"a4\")'>";
        echo "<i class='fas fa-file-alt os-format-icon'></i><span class='button-text'>" . __('Document', 'osfree') . "</span>";
        echo "</button>";
        echo "<button type='button' id='labelButton' class='os-format-btn' onclick='changeFormat(\"label\")'>";
        echo "<i class='fas fa-tag os-format-icon'></i><span class='button-text'>" . __('Label', 'osfree') . "</span>";
        echo "</button>";
        echo "</div>";

        echo "<button type='button' class='os-print-btn' id='printButton' onclick='printPDF()'>";
        echo "<i class='fas fa-print'></i><span class='button-text'>" . __('Print', 'osfree') . "</span>";
        echo "</button>";


        echo "</div>"; 
        echo "</div>"; 

        


        echo "<div class='os-content'>";

        echo "<div id='loading' class='os-loading'>";
        echo "<div class='os-spinner'></div>";
        echo "<div class='os-loading-text'>" . __('Loading document...', 'osfree') . "</div>";
        echo "</div>";

        echo "<div id='pdfViewer' class='fade-effect'></div>";

        echo "</div>"; 

        echo "<script src='https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js' defer></script>";

        echo "<script type='text/javascript'>

    var urlA4 = '{$urlA4}';
    var urlLabel = '{$urlLabel}';
    var currentPdfDocument = null;
    var currentPdfUrl = urlA4;
    var pdfPages = [];
    var currentPageNum = 1;
    var totalPages = 0;
    var pdfScale = 1.0;
    var pdfLoadAttempted = false;


    function isPdfJsLoaded() {
        return typeof pdfjsLib !== 'undefined';
    }

    function changeFormat(format) {
        
        var a4Button = document.getElementById('a4Button');
        var labelButton = document.getElementById('labelButton');
        var docIcon = document.querySelector('.os-document-icon i');
        
        if (a4Button) a4Button.classList.remove('active');
        if (labelButton) labelButton.classList.remove('active');
        
        if (format === 'a4') {
            
            if (a4Button) a4Button.classList.add('active');
            if (docIcon) docIcon.className = 'fas fa-file-pdf';
            loadPDF(urlA4);
        } else if (format === 'label') {

            if (labelButton) labelButton.classList.add('active');
            if (docIcon) docIcon.className = 'fas fa-tag';
            loadPDF(urlLabel);
        }
    }

    function printPDF() {
        if (currentPdfUrl) {
            window.open(currentPdfUrl, '_blank').focus();
        } else {
            console.error('Nenhum PDF carregado para imprimir');
        }
    }

    function showLoading() {
        var loading = document.getElementById('loading');
        var pdfViewer = document.getElementById('pdfViewer');
        if (loading) loading.classList.add('active');
        if (pdfViewer) pdfViewer.classList.remove('show');
    }

    function hideLoading() {
        var loading = document.getElementById('loading');
        var pdfViewer = document.getElementById('pdfViewer');
        if (loading) loading.classList.remove('active');
        if (pdfViewer) pdfViewer.classList.add('show');
    }

    function renderPage(pdf, pageNumber, container) {
        if (!isPdfJsLoaded()) {
            console.error('PDF.js não está carregado');
            return Promise.reject('PDF.js não está carregado');
        }
        
        return pdf.getPage(pageNumber).then(function(page) {
            var viewport = page.getViewport({scale: pdfScale});
            
            var pageDiv = document.createElement('div');
            pageDiv.className = 'page';
            pageDiv.setAttribute('data-page-number', pageNumber);
            
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            pageDiv.style.width = Math.floor(viewport.width) + 'px';
            pageDiv.style.height = Math.floor(viewport.height) + 'px';
            
            container.appendChild(pageDiv);
            pageDiv.appendChild(canvas);
            
            var renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            
            return page.render(renderContext).promise.then(function() {
                return pageDiv;
            });
        });
    }

    function loadPDF(url) {
        
        pdfLoadAttempted = true;
        
        if (!isPdfJsLoaded()) {
            console.warn('PDF.js não está carregado, usando iframe como fallback');
            
            var container = document.getElementById('pdfViewer');
            container.innerHTML = '<iframe src=\"' + url + '\" style=\"width:100%;height:100%;border:none;\" onload=\"hideLoading()\"></iframe>';
            showLoading();
            
            setTimeout(function() {
                hideLoading();
            }, 1000);
            
            return;
        }
        
        showLoading();
        currentPdfUrl = url;
        
        var container = document.getElementById('pdfViewer');
        container.innerHTML = '';
        
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
                return response.blob();
            })
            .then(blob => {

                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                    cMapPacked: true
                });
                
                loadingTask.promise.then(function(pdf) {

                    currentPdfDocument = pdf;
                    totalPages = pdf.numPages;
                    
                    var pagesContainer = document.createElement('div');
                    pagesContainer.className = 'pdfViewer';
                    container.appendChild(pagesContainer);
                    
                    var pendingPromises = [];
                    for (var i = 1; i <= totalPages; i++) {
                        var promise = renderPage(pdf, i, pagesContainer);
                        pendingPromises.push(promise);
                    }
                    
                    Promise.all(pendingPromises).then(function() {

                        hideLoading();
                    }).catch(function(error) {
                        console.error('Erro ao renderizar páginas:', error);
                        container.innerHTML = '<iframe src=\"' + url + '\" style=\"width:100%;height:100%;border:none;\"></iframe>';
                        hideLoading();
                    });
                }).catch(function(error) {
                    console.error('Erro ao processar o PDF:', error);
                    container.innerHTML = '<iframe src=\"' + url + '\" style=\"width:100%;height:100%;border:none;\"></iframe>';
                    hideLoading();
                });
            })
            .catch(function(error) {
                console.error('Erro ao acessar URL do PDF:', error);
                hideLoading();
                
                container.innerHTML = '<div style=\"text-align: center; color: #666; padding: 50px;\"><h3>Erro ao carregar documento</h3><p>Não foi possível acessar o arquivo: ' + error.message + '</p><p><small>URL: ' + url + '</small></p></div>';
            });
    }

    function initViewer() {
        if (!pdfLoadAttempted) {

            loadPDF(urlA4);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {

        showLoading();
        
        setTimeout(initViewer, 100);
        
        var checkPdfJsInterval = setInterval(function() {
            if (isPdfJsLoaded()) {
                clearInterval(checkPdfJsInterval);

                initViewer();
            }
        }, 100);
        
        setTimeout(function() {
            if (!isPdfJsLoaded() && !pdfLoadAttempted) {
                console.warn('PDF.js não carregou após 3 segundos, usando iframe como fallback');
                clearInterval(checkPdfJsInterval);
                
                var container = document.getElementById('pdfViewer');
                container.innerHTML = '<iframe src=\"' + urlA4 + '\" style=\"width:100%;height:100%;border:none;\"></iframe>';
                hideLoading();
                pdfLoadAttempted = true;
            }
        }, 3000);
    });

    window.onload = function() {
        setTimeout(initViewer, 200);
    };

    setTimeout(function() {
        initViewer();
    }, 1500);

    </script>";

        echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>";

        echo "</div>"; 
    }
}
