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
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

if (!function_exists('osfreeBuildDataUri')) {
    function osfreeBuildDataUri(?string $path): string
    {
        if (!$path || !is_file($path) || !is_readable($path)) {
            return '';
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($path) : null;
        if (!$mime || strpos($mime, 'image/') !== 0) {
            $mime = 'image/png';
        }

        $data = @file_get_contents($path);
        if ($data === false) {
            return '';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
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

function osfreeRenderRichText($text)
{
    if ($text === null || is_array($text) || is_object($text)) {
        return '';
    }

    $text = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $allowedTags = '<p><br><b><i><u><strong><em><ul><ol><li><table><tr><td><th><thead><tbody><img>';

    return strip_tags($text, $allowedTags);
}

try {
    include('../../../inc/includes.php');
    include('configOs.php');

    $pluginConfig = PluginOsfreeConfig::getConfig();

    $labelWidth = $pluginConfig['label_width'];
    $labelCustomText = $pluginConfig['label_custom_text'];
    $currency = $pluginConfig['currency'] ?? 'BRL';

    if (!in_array($labelWidth, [50, 60, 70, 80, 90, 100])) {
        $labelWidth = 60; 
    }

    $scaleFactor = $labelWidth / 60; 
    $fontScale = min(1.3, max(0.9, $scaleFactor)); 

    $baseFontSize = 10; 
    $baseTitleSize = 14; 
    $baseSmallSize = 8;  

    $fontSize = round($baseFontSize * $fontScale);
    $titleSize = round($baseTitleSize * $fontScale);
    $smallSize = round($baseSmallSize * $fontScale);

    $qrCodeSize = round(30 + ($labelWidth - 60) * 0.75); 
    $labelPadding = round(2 * ($labelWidth / 60)); 
    $labelWidth_info = ($labelWidth - 2 * $labelPadding); 
    $infoLabelWidth = round(($labelWidth >= 80) ? 35 : ($labelWidth >= 70 ? 30 : 25)); 

    $vendorDir = __DIR__ . '/../vendor';

    if (file_exists($vendorDir . '/autoload.php')) {
        require_once $vendorDir . '/autoload.php';
    } else {
        throw new Exception(__('Composer autoload not found', 'osfree'));
    }

    include('../inc/qrcode/vendor/autoload.php');

    global $DB;
    Session::checkLoginUser();

    $OsCustomerName = '';
    if (isset($ticketData['entities_id']) && class_exists('PluginOsfreeEntity')) {
        $entityConfig = PluginOsfreeEntity::getEntityConfig($ticketData['entities_id']);
        if ($entityConfig) {
            $OsCustomerName = $entityConfig['company_name'] ?? '';
        }
    }

    if (empty($OsCustomerName)) {
        $OsCustomerName = $entityData['name'] ?? ($EntidadeName ?? __('Not informed', 'osfree'));
    }

    $url = rtrim($CFG_GLPI['url_base'], '/'); 
    $url2 = "/front/ticket.form.php?id=" . $OsId;

    $qrImageData = '';
    $qrContent = "$url$url2";
    $qrStorageDir = defined('GLPI_PLUGIN_DOC_DIR')
        ? rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR) . '/osfree/qrcodes'
        : null;

    if ($qrStorageDir && !is_dir($qrStorageDir)) {
        @mkdir($qrStorageDir, 0755, true);
    }

    $qrFilePath = '';
    if ($qrStorageDir && is_dir($qrStorageDir) && is_writable($qrStorageDir)) {
        $qrFilePath = $qrStorageDir . '/qr_os_label_' . ($OsId ?? uniqid()) . '.png';
    }

    try {
        $options = new QROptions([
            'version' => 4,
            'eccLevel' => QRCode::ECC_L,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false
        ]);

        $qrCode = new QRCode($options);

        if (!empty($qrFilePath)) {
            $qrCode->render($qrContent, $qrFilePath);
            $qrImageData = osfreeBuildDataUri($qrFilePath);
        }

        if (empty($qrImageData)) {
            $qrBinary = $qrCode->render($qrContent);
            $qrImageData = 'data:image/png;base64,' . base64_encode($qrBinary);
        }
    } catch (Exception $qrException) {
        $qrImageData = '';
    }

    $primary = '#000000';    
    $secondary = '#000000';  
    $accent = '#000000';     
    $light = '#FFFFFF';      
    $dark = '#000000';       
    $tempDir = sys_get_temp_dir();

    $labelHeight = round($labelWidth * 1.8);

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => [$labelWidth, 450],
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 0,
        'margin_bottom' => 0,
        'tempDir' => $tempDir
    ]);

    $mpdf->SetDisplayMode('fullpage');
    $mpdf->SetCompression(true);
    $mpdf->setAutoTopMargin = 'stretch';
    $mpdf->setAutoBottomMargin = 'stretch';

    $mpdf->SetTitle(sprintf(__('%1$s #%2$s', 'osfree'), __('WO', 'osfree'), $OsId));

    $mpdf->SetCreator('Plugin OS - GLPI');
    $mpdf->SetDisplayMode('fullpage');

    $mpdf->SetHTMLHeader('');
    $mpdf->SetHTMLFooter('');

    $html = '
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            font-size: ' . $fontSize . 'pt;
            line-height: 1.3;
            color: ' . $secondary . ';
            padding: ' . $labelPadding . 'mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: ' . ($labelPadding * 0.8) . 'mm;
        }
        
        .os-title {
            font-size: ' . $titleSize . 'pt;
            font-weight: bold;
            color: ' . $primary . ';
            text-align: center;
            padding: ' . ($labelPadding * 0.5) . 'mm 0;
        }
        
        .os-date {
            font-size: ' . ($fontSize * 0.9) . 'pt;
            color: ' . $accent . ';
            text-align: center;
        }
        
        .qr-section {
            width: 100%;
            text-align: center;
            margin-bottom: ' . ($labelPadding * 1.5) . 'mm;
            float: none;
        }

        .qr-code {
            width: ' . $qrCodeSize . 'mm;
            height: auto;
            margin: 0 auto;
            display: block;
        }
        
        .divider {
            border-top: ' . ($scaleFactor * 0.5) . 'mm solid #000;
            margin: ' . ($labelPadding) . 'mm 0;
        }
        
        .info-section {
            width: ' . $labelWidth_info . 'mm;
            float: left;
        }
        
        .clearfix {
            clear: both;
            display: block;
            content: "";
        }
        
        .info-row {
            margin: ' . ($labelPadding * 0.5) . 'mm 0;
            display: table;
            width: 100%;
        }
        
        .info-label {
            font-weight: bold;
            width: ' . $infoLabelWidth . 'mm;
            display: table-cell;
            font-size: ' . ($fontSize) . 'pt;
        }
        
        .info-value {
            display: table-cell;
            font-size: ' . ($fontSize) . 'pt;
        }
        
        .ticket-title {
            font-weight: bold;
            margin: ' . ($labelPadding) . 'mm 0;
            text-align: center;
            font-size: ' . ($fontSize) . 'pt;
        }
    </style>';

    $html .= '<body>';

    $useHorizontalLayout = ($labelWidth >= 80); 

    if ($useHorizontalLayout) {
        $html .= '<div style="width: 100%; text-align: center; margin-bottom: ' . ($labelPadding) . 'mm;">';
        $html .= '<div style="font-size: ' . $titleSize . 'pt; font-weight: bold; margin-bottom: ' . ($labelPadding * 0.5) . 'mm;">' . htmlspecialchars($EmpresaPlugin) . '</div>';
        $html .= '<div style="font-size: ' . ($fontSize) . 'pt; margin-bottom: ' . ($labelPadding * 0.3) . 'mm;">' . __('EIN', 'osfree') . ': ' . htmlspecialchars($CnpjPlugin) . '</div>';
        $html .= '<div style="font-size: ' . ($smallSize) . 'pt;">' . htmlspecialchars($EnderecoPlugin) . ', ' . htmlspecialchars($CidadePlugin) . '</div>';
        $html .= '</div>';

        $html .= '<div style="display: flex; justify-content: space-between; margin-bottom: ' . ($labelPadding) . 'mm;">';

        $html .= '<div style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10mm; text-align: center;">';
        $html .= '<div style="flex: 1; display: flex; justify-content: center; align-items: center;">';
        if (!empty($qrImageData)) {
            $html .= '<img class="qr-code" src="' . $qrImageData . '" alt="QR Code" style="max-width: ' . ($qrCodeSize) . 'mm; margin: 0 auto;">';
        }
        $html .= '</div>';

        $html .= '<div style="flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">';
        $html .= '<div class="os-title" style="font-size:' . ($titleSize * 1.5) . 'pt; font-weight: bold; text-align: center;">' . $OsId . '</div>';
        $html .= '<div class="os-date" style="font-size:' . ($fontSize) . 'pt; text-align: center;">' . $DataOs . '</div>';
        $html .= '</div>';

        $html .= '</div>'; 
    } else {
        $html .= '<div class="company-section" style="text-align: center; margin-bottom: ' . ($labelPadding * 1.5) . 'mm;">
        <div style="font-size: ' . $titleSize . 'pt; font-weight: bold; margin-bottom: ' . ($labelPadding * 0.5) . 'mm;">' . htmlspecialchars($EmpresaPlugin) . '</div>
        <div style="font-size: ' . ($fontSize) . 'pt; margin-bottom: ' . ($labelPadding * 0.5) . 'mm;">' . __('EIN', 'osfree') . ': ' . htmlspecialchars($CnpjPlugin) . '</div>
        <div style="font-size: ' . ($smallSize) . 'pt;">' . htmlspecialchars($EnderecoPlugin) . ', ' . htmlspecialchars($CidadePlugin) . '</div>
    </div>';

        $html .= '<div class="qr-section" style="text-align: center; margin-bottom: 0mm;">';
        if (!empty($qrImageData)) {
            $html .= '<img class="qr-code" src="' . $qrImageData . '" alt="QR Code">';
        }
        $html .= '</div>';

        $html .= '
    <div class="header">
        <div class="os-title" style="font-size:' . ($titleSize * 0.85) . 'pt; text-align: center;">' . $OsId . '</div>
        <div class="os-date" style="font-size:' . ($smallSize) . 'pt; text-align: center;">' . $DataOs . '</div>
    </div>';
    }
    $html .= '<div class="divider"></div>';

    if ($useHorizontalLayout && !empty($OsCustomerName) && (!empty($UserName) || !empty($OsResponsavel))) {
        $html .= '<div style="display: flex; justify-content: space-between;">';

        $html .= '<div style="width: 48%;">';
        if (!empty($OsCustomerName)) {
            $html .= '<div class="info-row">';
            $html .= '<div class="info-label">' . __('Customer', 'osfree') . ':</div>';
            $html .= '<div class="info-value">' . osfreeRenderRichText($OsCustomerName) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<div style="width: 48%;">';
        if (!empty($UserName)) {
            $html .= '<div class="info-row">';
            $html .= '<div class="info-label">' . __('Requester', 'osfree') . ':</div>';
            $html .= '<div class="info-value">' . htmlspecialchars($UserName) . '</div>';
            $html .= '</div>';
        }

        if (!empty($OsResponsavel)) {
            $html .= '<div class="info-row">';
            $html .= '<div class="info-label">' . __('Technician', 'osfree') . ':</div>';
            $html .= '<div class="info-value">' . htmlspecialchars($OsResponsavel) . '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '</div>'; 
    } else {
        $html .= '<div class="info-section">';

        if (!empty($OsCustomerName)) {
            $html .= '
        <div class="info-row">
            <div class="info-label">' . __('Customer', 'osfree') . ':</div>
            <div class="info-value">' . osfreeRenderRichText($OsCustomerName) . '</div>
        </div>';
        }

        if (!empty($UserName)) {
            $html .= '
        <div class="info-row">
            <div class="info-label">' . __('Requester', 'osfree') . ':</div>
            <div class="info-value">' . htmlspecialchars($UserName) . '</div>
        </div>';
        }

        if (!empty($OsResponsavel)) {
            $html .= '
        <div class="info-row">
            <div class="info-label">' . __('Technician', 'osfree') . ':</div>
            <div class="info-value">' . htmlspecialchars($OsResponsavel) . '</div>
        </div>';
        }

        $html .= '</div>';
    }



    if (!empty($labelCustomText)) {
        $html .= '<div style="text-align: center; margin-top: ' . ($labelPadding * 3) . 'mm; font-style: italic; font-size: ' . ($fontSize) . 'pt; color: ' . $secondary . ';">' .
            htmlspecialchars($labelCustomText) .
            '</div>';
    }

    $html .= '</body>';

    $mpdf->WriteHTML($html);

    $fileName = sprintf(__('%1$s-%2$s.pdf', 'osfree'), __('WO', 'osfree'), $OsId);

    $download = isset($_GET['download']) ? (int)$_GET['download'] : 0;

    header('Content-Type: application/pdf');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    if ($download) {
        $mpdf->Output($fileName, \Mpdf\Output\Destination::DOWNLOAD);
    } else {
        $mpdf->Output($fileName, \Mpdf\Output\Destination::INLINE);
    }

    exit;
} catch (Exception $e) {
    echo '<h1>' . __('Error generating label', 'osfree') . '</h1>';
    echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
}
