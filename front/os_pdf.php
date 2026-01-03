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
ini_set('memory_limit', '256M');

include('../../../inc/includes.php');
include('configOs.php');

$vendorDir = __DIR__ . '/../vendor';
if (file_exists($vendorDir . '/autoload.php')) {
    require_once $vendorDir . '/autoload.php';
} else {
    die(__('Error: Composer autoload not found. Run \"composer install\" in the plugin root directory.', 'osfree'));
}

if (class_exists('PluginOsfreeColors')) {
    $colors = PluginOsfreeColors::getColors();
    $primary = $colors['primary'];
    $secondary = $colors['secondary'];
    $accent = $colors['accent'];
    $light = $colors['light'];
} else {
    $primary = '#2563EB';
    $secondary = '#0F172A';
    $accent = '#F59E0B';
    $light = '#F8FAFC';
}

$pluginConfig = PluginOsfreeConfig::getConfig();
$labelWidth = $pluginConfig['label_width'];
$labelCustomText = $pluginConfig['label_custom_text'];
$currency = $pluginConfig['currency'] ?? 'BRL';

include('../inc/qrcode/vendor/autoload.php');
global $DB, $CFG_GLPI;
Session::checkLoginUser();


$viewer = isset($_GET['viewer']) ? (int)$_GET['viewer'] : 0;
$download = isset($_GET['download']) ? (int)$_GET['download'] : 0;
$print = isset($_GET['print']) ? (int)$_GET['print'] : 0;

$baseUrl = $CFG_GLPI['url_base'] ?? '';
$rootDoc = $CFG_GLPI['root_doc'] ?? '';

if (empty($baseUrl)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $baseUrl = rtrim(sprintf('%s://%s%s', $scheme, $host, $rootDoc), '/');
}

$ticketPath = "/front/ticket.form.php?id=" . ($_GET['id'] ?? '');

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$options = new QROptions([
    'version' => 5,
    'eccLevel' => QRCode::ECC_L,
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'imageBase64' => true
]);

$qrStorageDir = null;
$qrFilePath = '';
if (defined('GLPI_PLUGIN_DOC_DIR')) {
    $qrStorageDir = rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR) . '/osfree/qrcodes';
    if (!is_dir($qrStorageDir)) {
        @mkdir($qrStorageDir, 0755, true);
    }
}

$qrContent = $baseUrl . $ticketPath;
if ($qrStorageDir && is_dir($qrStorageDir) && is_writable($qrStorageDir)) {
    $qrFilename = 'qr_os_' . ($_GET['id'] ?? uniqid()) . '.png';
    $qrFilePath = $qrStorageDir . '/' . $qrFilename;
    (new QRCode($options))->render($qrContent, $qrFilePath);
}

if (!empty($qrFilePath) && is_file($qrFilePath)) {
    $qrCodeDataUri = 'data:image/png;base64,' . base64_encode(file_get_contents($qrFilePath));
} else {
    $qrCodeDataUri = (new QRCode($options))->render($qrContent);
}

$pluginDocDir = defined('GLPI_PLUGIN_DOC_DIR') ? rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR) : null;
$logoStorageDir = $pluginDocDir ? $pluginDocDir . '/osfree/logo' : null;
$customLogoPath = '';
if ($logoStorageDir && is_dir($logoStorageDir)) {
    $matches = glob($logoStorageDir . '/logo_os.*');
    if ($matches) {
        foreach ($matches as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                $customLogoPath = $candidate;
                break;
            }
        }
    }
}
$defaultLogoPath = realpath(__DIR__ . '/../pics/logo_os.png');
$logoPath = $customLogoPath ?: $defaultLogoPath;

function cleanHtmlText($text)
{
    if ($text === null || is_array($text) || is_object($text)) {
        return '';
    }

    $text = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $allowedTags = '<p><br><b><i><u><strong><em><ul><ol><li><table><tr><td><th><thead><tbody><img>';
    $text = strip_tags($text, $allowedTags);

    return $text;
}

function getCurrencySymbol($currencyCode)
{
    $symbols = [
        'BRL' => 'R$',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'ARS' => '$',
        'CLP' => '$',
        'COP' => '$',
        'PEN' => 'S/',
        'UYU' => '$U'
    ];

    return $symbols[$currencyCode] ?? $currencyCode;
}

function formatCurrencyValue($value, $currencyCode)
{
    $symbol = getCurrencySymbol($currencyCode);

    switch ($currencyCode) {
        case 'BRL':
        case 'ARS':
        case 'CLP':
        case 'COP':
        case 'UYU':
            return $symbol . ' ' . $value;
        case 'USD':
        case 'GBP':
            return $symbol . $value;
        case 'EUR':
            return $value . ' ' . $symbol;
        case 'PEN':
            return $symbol . ' ' . $value;
        default:
            return $symbol . ' ' . $value;
    }
}

$companyName = '';
$companyDocument = '';
$documentLabel = __('Document', 'osfree');

if (isset($ticketData['entities_id'])) {
    if (class_exists('PluginOsfreeEntity')) {
        $entityConfig = PluginOsfreeEntity::getEntityConfig($ticketData['entities_id']);
        if ($entityConfig) {
            $companyName = $entityConfig['company_name'];
            $companyDocument = PluginOsfreeEntity::formatDocument($entityConfig['rn']);
        }

        $docConfig = PluginOsfreeEntity::getDocumentConfig();
        $documentLabel = $docConfig['label'];
    }
}

if (empty($companyName)) {
    $companyName = isset($EntidadeName) ? $EntidadeName : __('Not informed', 'osfree');
}

if (empty($companyDocument)) {
    $companyDocument = isset($EntityRn) ? $EntityRn : '';
}

try {
    $iterator = $DB->request([
        'FROM' => 'glpi_plugin_os_colors',
        'LIMIT' => 1
    ]);

    $primary = '#2563EB';
    $secondary = '#0F172A';
    $accent = '#F59E0B';
    $light = '#F8FAFC';

    if (count($iterator) > 0) {
        $colorData = $iterator->current();
        $primary = $colorData['primary_color'] ?? '#2563EB';
        $secondary = $colorData['secondary_color'] ?? '#0F172A';
        $accent = $colorData['accent_color'] ?? '#F59E0B';
        $light = $colorData['light_color'] ?? '#F8FAFC';
    }
} catch (Exception $e) {
    $primary = '#2563EB';
    $secondary = '#0F172A';
    $accent = '#F59E0B';
    $light = '#F8FAFC';
}

$success = '#10B981';    
$danger = '#EF4444';     
$lighter = '#F1F5F9';    
$medium = '#E2E8F0';     
$dark = '#64748B';       
$white = '#FFFFFF';      

$stylesheet = '
<style>
    
    body {
        font-family: "Helvetica", "Arial", sans-serif;
        font-size: 8.5pt;
        line-height: 1.2;
        color: ' . $secondary . ';
        margin: 0;
        padding: 0;
    }
    
    * {
        box-sizing: border-box;
    }
    
    .document-section {
        margin-bottom: 5px;
        clear: both;
    }
    
    .section-header {
        background-color: ' . $primary . ';
        color: white;
        padding: 3px 6px;
        font-size: 8.5pt;
        font-weight: 600;
        border-radius: 3px 3px 0 0;
        position: relative;
    }
    
    .section-body {
        background-color: ' . $light . ';
        border: 1px solid ' . $medium . ';
        border-top: none;
        border-radius: 0 0 3px 3px;
        padding: 4px 6px;
        position: relative;
    }

    .clearfix:after {
        content: "";
        display: table;
        clear: both;
    }
    
    .footer-page {
        font-size: 7pt;
        color: ' . $dark . ';
        text-align: center;
    }
</style>';

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 8,
        'margin_right' => 8,
        'margin_top' => 20,
        'margin_bottom' => 6,
        'margin_header' => 4,
        'margin_footer' => 4,
        'tempDir' => sys_get_temp_dir(),
    ]);

    $mpdf->SetTitle(__('WO', 'osfree') . ' #' . $OsId);
    $mpdf->SetAuthor($OsResponsavel);
    $mpdf->SetCreator('Plugin OS - GLPI');

    $footer = '<div class="footer-page">' . sprintf(__('Page %s of %s', 'osfree'), '{PAGENO}', '{nbpg}') . '</div>';
    $mpdf->SetHTMLFooter($footer);

    $html = $stylesheet . '<body>';

    $logoTag = '';
    if ($logoPath && is_file($logoPath)) {
        $logoTag = '<img src="' . $logoPath . '" style="height:45px; max-width:100%;" alt="Logo">';
    }

    $headerHTML = '
<table style="width:100%; border-collapse: collapse; margin-bottom: 6px;">
    <tr>
        <!-- Logo (15%) -->
        <td style="width:15%; vertical-align: middle;">
            ' . $logoTag . '
        </td>
        
        <!-- Nome e dados (65%) -->
        <td style="width:65%; vertical-align: middle; text-align: center;">
            <div style="font-size:12pt; font-weight:700; color:' . $accent . '; text-transform:uppercase; margin-bottom:2px; line-height:1;">' . htmlspecialchars($EmpresaPlugin) . '</div>
            <div style="font-size:7pt; color:' . $dark . '; line-height:1.1;">'
        . sprintf(__('EIN: %1$s · Phone: %2$s · %3$s, %4$s', 'osfree'), htmlspecialchars($CnpjPlugin), htmlspecialchars($TelefonePlugin), htmlspecialchars($EnderecoPlugin), htmlspecialchars($CidadePlugin)) .
        '</div>
        </td>
        
        <!-- QR e OS (20%) -->
        <td style="width:20%; vertical-align: middle;">
            <table style="width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:60%; vertical-align: middle; text-align:right; padding-right:4px;">
                        <div style="text-align:right;">
                            <div style="font-size:7pt; color:' . $dark . ';">' . __('WO Nº', 'osfree') . '</div>
                            <div style="font-size:12pt; font-weight:700; color:' . $accent . '; line-height:1;">' . $OsId . '</div>
                            <div style="font-size:7pt; color:' . $dark . ';">' . $DataOs . '</div>
                        </div>
                    </td>
                    <td style="width:40%; vertical-align: middle; text-align:center;">
                        <img src="' . $qrCodeDataUri . '" style="width:52px; height:auto; display:block;" alt="QR Code">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>';

    $mpdf->SetHTMLHeader($headerHTML);

    $html .= '
<div class="document-section">
    <div class="section-header">' . __('CUSTOMER DATA', 'osfree') . '</div>
    <div class="section-body" style="padding: 6px;">
        <table style="width:100%; border-collapse: collapse; margin:0; padding:0;">
            <!-- Linha 1: Cliente e CNPJ/EIN -->
            <tr>
                <td style="padding:0 0 2px 0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-spacing: 2px 0;">
                        <tr>
                            <td style="width:70%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Customer', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . cleanHtmlText($companyName) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:30%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . htmlspecialchars($documentLabel) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($companyDocument) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Linha 2: Telefone, Email e CEP -->
            <tr>
                <td style="padding:0 0 2px 0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-spacing: 2px 0;">
                        <tr>
                            <td style="width:25%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Phone', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($EntidadePhone) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:55%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Email', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($EntidadeEmail) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:20%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('ZIP', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($EntidadeCep) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Linha 3: Endereço -->
            <tr>
                <td style="padding:0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <tr>
                            <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                ' . __('Address', 'osfree') . '
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                ' . htmlspecialchars($EntidadeEndereco) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>';

    $html .= '
<div class="document-section">
    <div class="section-header">' . __('TICKET DETAILS', 'osfree') . '</div>
    <div class="section-body" style="padding: 6px;">
        <table style="width:100%; border-collapse: collapse; margin:0; padding:0;">
            <!-- Linha 1: Título do Chamado -->
            <tr>
                <td style="padding:0 0 2px 0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                        <tr>
                            <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                ' . __('Ticket Title', 'osfree') . '
                            </td>
                        </tr>
                        <tr>
                            <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                ' . htmlspecialchars($OsNome) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Linha 2: Técnico e Solicitante -->
            <tr>
                <td style="padding:0 0 2px 0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-spacing: 2px 0;">
                        <tr>
                            <td style="width:50%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Technician in Charge', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($OsResponsavel) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:50%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Requester', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($UserName) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <!-- Linha 3: Data de abertura e Data de fechamento -->
            <tr>
                <td style="padding:0; width:100%;">
                    <table style="width:100%; border-collapse: separate; border-spacing: 2px 0;">
                        <tr>
                            <td style="width:50%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Opening Date', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($OsData) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td style="width:50%; padding:0;">
                                <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <tr>
                                        <td style="background-color: ' . $lighter . '; border: 1px solid ' . $medium . '; border-radius: 3px 3px 0 0; padding: 3px 6px; font-size: 7.5pt; font-weight: 600; color: ' . $secondary . ';">
                                            ' . __('Closing Date', 'osfree') . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-top: none; border-radius: 0 0 3px 3px; padding: 3px 6px; font-size: 8.5pt; color: ' . $secondary . '; height: 20px;">
                                            ' . htmlspecialchars($OsDataEntrega) . '
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>';
    $html .= '
<div class="document-section">
    <div class="section-header">' . __('PROBLEM DESCRIPTION', 'osfree') . '</div>
    <div class="section-body" style="padding: 6px;">
        <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <tr>
                <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-radius: 3px; padding: 6px 8px; font-size: 8.5pt; color: ' . $secondary . '; min-height: 80px; white-space: pre-wrap;">
                    ' . cleanHtmlText($OsDescricao) . '
                </td>
            </tr>
        </table>
    </div>
</div>';

    $html .= '
<div class="document-section">
    <div class="section-header">' . __('SOLUTION', 'osfree') . '</div>
    <div class="section-body" style="padding: 6px;">';

    if (!isset($OsSolucao) || empty($OsSolucao)) {
        $html .= '
        <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <tr>
                <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-radius: 3px; padding: 6px 8px; min-height: 130px; height: 130px; position: relative; vertical-align: top;">
                    <div style="position: absolute; top: 50%; left: 0; right: 0; text-align: center; transform: translateY(-50%); font-style: italic; font-size: 7pt; color: rgba(100,116,139,0.4); pointer-events: none;">
                        ' . __('Space for solution description', 'osfree') . '
                    </div>
                    <!-- Linhas guia para escrita manual -->
                    <div style="position: absolute; left: 8px; right: 8px; bottom: 8px; top: 8px;">
                        <div style="border-top: 1px solid ' . $medium . '; opacity: 0.25; margin-top: 20px;"></div>
                        <div style="border-top: 1px solid ' . $medium . '; opacity: 0.25; margin-top: 20px;"></div>
                        <div style="border-top: 1px solid ' . $medium . '; opacity: 0.25; margin-top: 20px;"></div>
                        <div style="border-top: 1px solid ' . $medium . '; opacity: 0.25; margin-top: 20px;"></div>
                    </div>
                </td>
            </tr>
        </table>';
    } else {
        $html .= '
        <table style="width:100%; border-collapse: separate; border-radius: 3px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <tr>
                <td style="background-color: ' . $white . '; border: 1px solid ' . $medium . '; border-radius: 3px; padding: 6px 8px; font-size: 8.5pt; color: ' . $secondary . '; min-height: 80px; white-space: pre-line;">
                    ' . cleanHtmlText($OsSolucao) . '
                </td>
            </tr>
        </table>';
    }

    $html .= '
    </div>
</div>';

$html .= '
<div class="document-section" style="page-break-inside: avoid;">
    <div class="section-header">' . __('SIGNATURES', 'osfree') . '</div>
    <div class="section-body" style="padding: 6px;">
        <table style="width:100%; border-collapse: separate; border-spacing: 8px 0; margin-top: 5px;">
            <tr>
                <td style="width:50%; text-align:center; vertical-align:bottom; padding:0;">
                    <div style="font-family: monospace; font-size: 12pt; color: ' . $dark . '; width: 80%; margin: 0 auto 3px auto; overflow: hidden;">
                        _______________________________
                    </div>
                    <div style="font-weight:600; font-size:8pt; color:' . $secondary . '; line-height:1.2;">' . htmlspecialchars($OsResponsavel) . '</div>
                    <div style="font-size:7pt; color:' . $dark . '; line-height:1.2;">' . __('Technician in Charge', 'osfree') . '</div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:bottom; padding:0;">
                    <div style="font-family: monospace; font-size: 12pt; color: ' . $dark . '; width: 80%; margin: 0 auto 3px auto; overflow: hidden;">
                        _______________________________
                    </div>
                    <div style="font-weight:600; font-size:8pt; color:' . $secondary . '; line-height:1.2;">' . htmlspecialchars($UserName) . '</div>
                    <div style="font-size:7pt; color:' . $dark . '; line-height:1.2;">' . __('Customer', 'osfree') . '</div>
                </td>
            </tr>
        </table>
    </div>
</div>';


    $html .= '
<div style="margin-top: 6px; border-top: 1px solid ' . $medium . '; padding-top: 3px; font-size: 7pt; color:' . $dark . '; text-align: center;">'
        . sprintf(__('%1$s | Generated on: %2$s | Use the QR Code to verify authenticity', 'osfree'), $SitePlugin, date('d/m/Y H:i')) .
        '</div>';

    $html .= '</body>';

    $mpdf->WriteHTML($html);

    $fileName = __('WO', 'osfree') . $OsId . '.pdf';
    $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName); 

    header('Content-Type: application/pdf');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    if ($download) {
        $mpdf->Output($fileName, \Mpdf\Output\Destination::DOWNLOAD);
    } else if ($print) {
        $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
    } else {
        $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
    }
} catch (\Mpdf\MpdfException $e) {
    echo '<h1>' . __('Error generating PDF', 'osfree') . '</h1>';
    echo '<p>' . sprintf(__('An error occurred while generating the PDF: %s', 'osfree'), $e->getMessage()) . '</p>';
    echo '<p>' . __('Technical details:', 'osfree') . ' <pre>' . $e->getTraceAsString() . '</pre></p>';
}
