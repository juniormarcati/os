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
global $DB;
Session::checkRight("config", UPDATE);
Plugin::loadLang('osfree');
Html::header(__('Work Order', 'osfree'), "", "plugins", "osfree");

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


if (!isset($_SESSION['os_config_temp'])) {

    try {
        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_os_config',
            'LIMIT' => 1
        ]);

        if (count($iterator) > 0) {
            $_SESSION['os_config_temp']['company'] = $iterator->current();
        }

        $iterator = $DB->request([
            'FROM' => 'glpi_plugin_os_colors',
            'LIMIT' => 1
        ]);

        if (count($iterator) > 0) {
            $_SESSION['os_config_temp']['colors'] = $iterator->current();
        }
    } catch (Exception $e) {
    }
}

$EmpresaPlugin = $_SESSION['os_config_temp']['company']['name'] ?? '';
$CnpjPlugin = $_SESSION['os_config_temp']['company']['cnpj'] ?? '';
$EnderecoPlugin = $_SESSION['os_config_temp']['company']['address'] ?? '';
$TelefonePlugin = $_SESSION['os_config_temp']['company']['phone'] ?? '';
$CidadePlugin = $_SESSION['os_config_temp']['company']['city'] ?? '';
$SitePlugin = $_SESSION['os_config_temp']['company']['site'] ?? '';
$LabelWidth = $_SESSION['os_config_temp']['company']['label_width'] ?? '';
$LabelCustomText = $_SESSION['os_config_temp']['company']['label_custom_text'] ?? '';

$primaryColor = $_SESSION['os_config_temp']['colors']['primary_color'] ?? '#071A36';
$secondaryColor = $_SESSION['os_config_temp']['colors']['secondary_color'] ?? '#0F172A';
$accentColor = $_SESSION['os_config_temp']['colors']['accent_color'] ?? '#071A36';
$lightColor = $_SESSION['os_config_temp']['colors']['light_color'] ?? '#F8FAFC';

$currentStep = isset($_GET['step']) ? intval($_GET['step']) : 1;
if ($currentStep < 1 || $currentStep > 5) {
    $currentStep = 1;
}

$pluginLogoAbsolute = realpath(__DIR__ . '/../pics/logotipo.png');
$pluginLogoExists = $pluginLogoAbsolute && is_file($pluginLogoAbsolute);
$pluginLogoDisplayPath = $pluginLogoExists ? osfreeBuildDataUri($pluginLogoAbsolute) : '';

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

$defaultLogoAbsolute = realpath(__DIR__ . '/../pics/logo_os.png');
if (!$defaultLogoAbsolute || !is_file($defaultLogoAbsolute)) {
    $defaultLogoAbsolute = '';
}

$currentLogoPath = $customLogoPath ?: $defaultLogoAbsolute;
$osLogoExists = !empty($currentLogoPath);
$osLogoDisplayPath = $osLogoExists ? osfreeBuildDataUri($currentLogoPath) : '';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .os-premium-wrapper {
        margin: 24px 0 32px 0;
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        font-family: 'Segoe UI', Arial, sans-serif;
    }

    .os-premium-banner {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 20px 24px;
        background: linear-gradient(135deg, #00BAC4, #23A455);
        color: #ffffff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
    }

    .os-premium-banner::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.28), transparent 55%);
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
        background: rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        font-size: 0.78em;
        letter-spacing: 0.01em;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .os-premium-feature i {
        font-size: 0.9em;
    }

    .os-premium-feature:hover {
        background: rgba(255, 255, 255, 0.24);
        transform: translateY(-1px);
    }

    .os-premium-action {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 20px;
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        text-decoration: none;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.22);
        font-weight: 600;
        font-size: 0.9em;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
        transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
    }

    .os-premium-action i {
        font-size: 0.95em;
    }

    .os-premium-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 18px 30px rgba(0, 0, 0, 0.22);
        background: rgba(255, 255, 255, 0.22);
    }

    .os-config-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .os-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 25px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .license-warning {
        animation: slideDown 0.5s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .os-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .os-card-header {
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 20px;
        padding-bottom: 15px;
        position: relative;
    }

    .os-card-header h3 {
        color: #2c3e50;
        font-size: 18px;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .os-card-header h3 i {
        margin-right: 10px;
        color: #3498db;
    }

    .os-form-group {
        margin-bottom: 15px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .os-form-label {
        flex: 0 0 200px;
        font-weight: 500;
        color: #34495e;
    }

    .os-form-control {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: border-color 0.3s;
    }

    .os-form-control:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.25);
    }

    .os-btn {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .os-btn i {
        margin-right: 8px;
    }

    .os-btn:hover {
        background: #2980b9;
    }

    .os-btn-secondary {
        background: #7f8c8d;
    }

    .os-btn-secondary:hover {
        background: #6c7a7a;
    }

    .os-btn-outline {
        background: transparent;
        color: #3498db;
        border: 1px solid #3498db;
    }

    .os-btn-outline:hover {
        background: rgba(52, 152, 219, 0.1);
    }

    .os-logo-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        margin-bottom: 20px;
        border: 2px dashed #e0e0e0;
        border-radius: 8px;
        min-height: 120px;
    }

    .os-file-input {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .os-file-input input[type="file"] {
        display: block;
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .os-actions {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 30px;
    }

    .stepper {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        max-width: 150px;
        cursor: default;
    }

    .step:not(:last-child):after {
        content: '';
        position: absolute;
        top: 20px;
        right: -50%;
        width: 100%;
        height: 2px;
        background-color: #e0e0e0;
        z-index: 0;
    }

    .step.completed:not(:last-child):after,
    .step.active:not(:last-child):after {
        background-color: #3498db;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        position: relative;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .step.completed .step-icon {
        background-color: #2ecc71;
        color: white;
    }

    .step.active .step-icon {
        background-color: #3498db;
        color: white;
    }

    .step-label {
        font-size: 14px;
        color: #7f8c8d;
        text-align: center;
        transition: all 0.3s ease;
    }

    .step.completed .step-label {
        color: #2ecc71;
        font-weight: 500;
    }

    .step.active .step-label {
        color: #3498db;
        font-weight: 500;
    }

    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
        animation: fadeIn 0.5s ease;
    }

    .info-summary {
        background-color: #f9f9f9;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        display: flex;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        flex: 0 0 200px;
        font-weight: 500;
        color: #7f8c8d;
    }

    .info-value {
        flex: 1;
        color: #2c3e50;
    }

    .option-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }

    .option-card:hover {
        border-color: #3498db;
        background-color: rgba(52, 152, 219, 0.05);
    }

    .option-card.selected {
        border-color: #3498db;
        background-color: rgba(52, 152, 219, 0.1);
    }

    .option-icon {
        font-size: 24px;
        margin-right: 15px;
        color: #3498db;
    }

    .success-message {
        text-align: center;
        padding: 20px;
        margin-top: 20px;
    }

    .success-icon {
        font-size: 80px;
        color: #2ecc71;
        margin-bottom: 20px;
    }

    .color-picker-container {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .color-preview {
        width: 40px;
        height: 40px;
        margin-right: 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .color-picker {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        width: 50px;
        height: 50px;
        background-color: transparent;
        border: none;
        cursor: pointer;
    }

    .color-picker::-webkit-color-swatch {
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .color-picker::-moz-color-swatch {
        border-radius: 6px;
        border: 1px solid #ddd;
    }

    .color-info {
        flex: 1;
        margin-left: 15px;
    }

    .color-name {
        font-weight: 600;
        margin-bottom: 3px;
        color: #2c3e50;
    }

    .color-description {
        font-size: 12px;
        color: #7f8c8d;
        margin: 0;
    }

    .color-hex {
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 13px;
        color: #64748b;
        display: inline-block;
        margin-top: 5px;
    }

    .theme-preview {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        margin: 30px 0;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
    }

    .preview-header {
        padding: 10px 15px;
        font-weight: 600;
        color: white;
    }

    .preview-body {
        padding: 15px;
    }

    .preview-field {
        margin-bottom: 10px;
    }

    .preview-label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
        padding: 5px 10px;
        border-radius: 4px 4px 0 0;
    }

    .preview-value {
        padding: 8px 12px;
        border-radius: 0 0 4px 4px;
    }

    .color-presets {
        display: flex;
        margin-bottom: 30px;
        gap: 10px;
        flex-wrap: wrap;
    }

    .color-preset {
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .color-preset:hover {
        transform: translateY(-2px);
    }

    .color-preset.active {
        border-color: #3498db;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .preset-colors {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    .preset-color {
        width: 25px;
        height: 25px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .preset-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .os-form-group {
            flex-direction: column;
            align-items: flex-start;
        }

        .os-form-label {
            flex: 0 0 100%;
            margin-bottom: 5px;
        }

        .stepper {
            flex-direction: column;
            gap: 15px;
        }

        .step {
            flex-direction: row;
            max-width: 100%;
        }

        .step:not(:last-child):after {
            display: none;
        }

        .step-icon {
            margin-bottom: 0;
            margin-right: 10px;
        }

        .os-actions {
            flex-direction: column;
        }

        .info-item {
            flex-direction: column;
        }

        .info-label {
            flex: 0 0 100%;
            margin-bottom: 5px;
        }

        .os-premium-banner {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .os-premium-features {
            width: 100%;
        }

        .os-premium-feature {
            width: 100%;
            justify-content: flex-start;
        }

        .os-premium-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="os-premium-wrapper">
    <div class="os-premium-banner">
        <div class="os-premium-content">
            <span class="os-premium-eyebrow"><?php echo __('Premium', 'osfree'); ?></span>
            <div class="os-premium-title"><?php echo __('Upgrade Your Work Orders', 'osfree'); ?></div>
            <div class="os-premium-desc"><?php echo __('Experience a smarter workflow with these premium highlights:', 'osfree'); ?></div>
            <div class="os-premium-features">
                <span class="os-premium-feature"><i class="fas fa-coins"></i><?php echo __('Ticket cost insights', 'osfree'); ?></span>
                <span class="os-premium-feature"><i class="fas fa-pen-nib"></i><?php echo __('Digital signatures', 'osfree'); ?></span>
                <span class="os-premium-feature"><i class="fas fa-cubes"></i><?php echo __('Detailed ticket items', 'osfree'); ?></span>
                <span class="os-premium-feature"><i class="fas fa-sliders-h"></i><?php echo __('Advanced interface', 'osfree'); ?></span>
            </div>
            <div class="os-premium-footnote"><?php echo __('Gain more accuracy, productivity, and professionalism across every operation.', 'osfree'); ?></div>
        </div>
        <a class="os-premium-action" href="https://pluginos.marcati.com.br" target="_blank" rel="noopener">
            <i class="fas fa-star"></i><span><?php echo __('Discover Premium', 'osfree'); ?></span>
        </a>
    </div>
</div>

<div class="os-config-container">
    <div class="os-card">
        <div style="text-align: center; margin-bottom: 20px;">
            <?php if ($pluginLogoExists): ?>
                <img src="<?php echo $pluginLogoDisplayPath; ?>" alt="<?php echo __('Plugin Logo', 'osfree'); ?>" style="max-height: 80px;">
            <?php endif; ?>
            <h2 style="color: #2c3e50; margin-top: 15px;"><?php echo __('Plugin Configuration', 'osfree'); ?></h2>
            <p style="color: #7f8c8d;"><?php echo __('Configure your company information for display in Work Orders', 'osfree'); ?></p>
        </div>

        <div class="stepper">
            <div class="step <?php echo ($currentStep >= 1) ? 'active' : ''; ?> <?php echo ($currentStep > 1) ? 'completed' : ''; ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 1): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <i class="fas fa-building"></i>
                    <?php endif; ?>
                </div>
                <div class="step-label"><?php echo __('Company Information', 'osfree'); ?></div>
            </div>
            <div class="step <?php echo ($currentStep >= 2) ? 'active' : ''; ?> <?php echo ($currentStep > 2) ? 'completed' : ''; ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 2): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <i class="fas fa-image"></i>
                    <?php endif; ?>
                </div>
                <div class="step-label"><?php echo __('Logo', 'osfree'); ?></div>
            </div>
            <div class="step <?php echo ($currentStep >= 3) ? 'active' : ''; ?> <?php echo ($currentStep > 3) ? 'completed' : ''; ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 3): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <i class="fas fa-palette"></i>
                    <?php endif; ?>
                </div>
                <div class="step-label"><?php echo __('Theme Colors', 'osfree'); ?></div>
            </div>
            <div class="step <?php echo ($currentStep >= 4) ? 'active' : ''; ?> <?php echo ($currentStep > 4) ? 'completed' : ''; ?>">
                <div class="step-icon">
                    <?php if ($currentStep > 4): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <i class="fas fa-tag"></i>
                    <?php endif; ?>
                </div>
                <div class="step-label"><?php echo __('Label', 'osfree'); ?></div>
            </div>
            <div class="step <?php echo ($currentStep == 5) ? 'active' : ''; ?>">
                <div class="step-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label"><?php echo __('Finish', 'osfree'); ?></div>
            </div>
        </div>

        <div class="step-content <?php echo ($currentStep == 1) ? 'active' : ''; ?>">
            <div class="os-card">
                <div class="os-card-header">
                    <h3><i class="fas fa-building"></i> <?php echo __('Company Information', 'osfree'); ?></h3>
                </div>

                <form action="config_temp.php" method="post" id="companyInfoForm">
                    <input type="hidden" name="_glpi_csrf_token" value="<?php echo Session::getNewCSRFToken(); ?>">
                    <input type="hidden" name="step" value="1">

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('Company Name', 'osfree'); ?>:</label>
                        <input type="text" class="os-form-control" name="name_form" value="<?php echo htmlspecialchars($EmpresaPlugin); ?>" maxlength="256" required>
                    </div>

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('EIN', 'osfree'); ?>:</label>
                        <input type="text" class="os-form-control" name="cnpj_form" value="<?php echo htmlspecialchars($CnpjPlugin); ?>" maxlength="256">
                    </div>

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('Address', 'osfree'); ?>:</label>
                        <input type="text" class="os-form-control" name="address_form" value="<?php echo htmlspecialchars($EnderecoPlugin); ?>" maxlength="256">
                    </div>

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('Phone', 'osfree'); ?>:</label>
                        <input type="text" class="os-form-control" name="phone_form" value="<?php echo htmlspecialchars($TelefonePlugin); ?>" maxlength="256">
                    </div>

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('City/State', 'osfree'); ?>:</label>
                        <input type="text" class="os-form-control" name="city_form" value="<?php echo htmlspecialchars($CidadePlugin); ?>" maxlength="256">
                    </div>

                    <div class="os-form-group">
                        <label class="os-form-label"><?php echo __('Website', 'osfree'); ?>:</label>
                        <input type="url" class="os-form-control" name="site_form" value="<?php echo htmlspecialchars($SitePlugin); ?>" maxlength="256" placeholder="https://www.exemplo.com.br">
                    </div>
                    <div class="os-actions" style="<?php echo ($currentStep == 1) ? 'justify-content: flex-end;' : ''; ?>">
                        <?php if ($currentStep != 1): ?>
                            <a href="#" class="os-btn os-btn-secondary" onclick="history.back();">
                                <i class="fas fa-arrow-left"></i> <?php echo __('Previous', 'osfree'); ?>
                            </a>
                        <?php endif; ?>
                        <a href="#" class="os-btn" onclick="document.getElementById('companyInfoForm').submit();">
                            <i class="fas fa-arrow-right"></i> <?php echo __('Next', 'osfree'); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="step-content <?php echo ($currentStep == 2) ? 'active' : ''; ?>">
            <div class="os-card">
                <div class="os-card-header">
                    <h3><i class="fas fa-image"></i> <?php echo __('WO Logo', 'osfree'); ?></h3>
                </div>

                <div class="os-logo-options" style="margin-bottom: 30px;">
                    <?php if ($osLogoExists): ?>
                        <div class="option-card selected" id="keep-logo-option" onclick="selectLogoOption('keep-logo')">
                            <span class="option-icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <h4 style="margin: 0 0 5px 0;"><?php echo __('Use current logo', 'osfree'); ?></h4>
                                <p style="margin: 0; color: #7f8c8d;"><?php echo __('Keep the current logo without changes', 'osfree'); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="option-card <?php echo (!$osLogoExists) ? 'selected' : ''; ?>" id="upload-logo-option" onclick="selectLogoOption('upload-logo')">
                        <span class="option-icon"><i class="fas fa-upload"></i></span>
                        <div>
                            <h4 style="margin: 0 0 5px 0;"><?php echo __('Upload new logo', 'osfree'); ?></h4>
                            <p style="margin: 0; color: #7f8c8d;"><?php echo __('Upload a new logo for your company', 'osfree'); ?></p>
                        </div>
                    </div>

                    <div class="option-card" id="no-logo-option" onclick="selectLogoOption('no-logo')">
                        <span class="option-icon"><i class="fas fa-ban"></i></span>
                        <div>
                            <h4 style="margin: 0 0 5px 0;"><?php echo __('No Logo', 'osfree'); ?></h4>
                            <p style="margin: 0; color: #7f8c8d;"><?php echo __('Continue without adding a logo', 'osfree'); ?></p>
                        </div>
                    </div>
                </div>

                <div id="logo-upload-section" style="<?php echo (!$osLogoExists) ? 'display: block;' : 'display: none;'; ?>">
                    <div class="os-logo-preview">
                        <?php if ($osLogoExists): ?>
                            <img src="<?php echo $osLogoDisplayPath; ?>" alt="<?php echo __('Current logo', 'osfree'); ?>" style="max-width: 300px; max-height: 100px;">
                        <?php else: ?>
                            <div style="text-align: center; color: #bdc3c7;">
                                <i class="fas fa-image" style="font-size: 48px;"></i>
                                <p><?php echo __('No logo found', 'osfree'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px;">
                        <i class="fas fa-info-circle"></i> <?php echo __('Upload the logo (recommended size: 300x100px)', 'osfree'); ?>
                    </p>

                    <form method="post" enctype="multipart/form-data" action="insert_logo.php" id="logoForm">
                        <input type="hidden" name="from_wizard" value="1">
                        <input type="hidden" name="_glpi_csrf_token" value="<?php echo Session::getNewCSRFToken(); ?>">
                        <div class="os-form-group">
                            <div class="os-file-input">
                                <input type="file" name="arquivo" id="logo-file" accept="image/*">
                            </div>
                        </div>

                        <p style="text-align: center; color: #e74c3c; font-size: 13px; margin-top: 15px;">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo __('Your logo will be stored in the plugin image folder inside the GLPI files directory.', 'osfree'); ?>
                        </p>
                    </form>
                </div>

                <div id="current-logo-section" style="<?php echo ($osLogoExists) ? 'display: block;' : 'display: none;'; ?>">
                    <div class="os-logo-preview">
                        <?php if ($osLogoExists): ?>
                            <img src="<?php echo $osLogoDisplayPath; ?>" alt="<?php echo __('Current logo', 'osfree'); ?>" style="max-width: 300px; max-height: 100px;">
                        <?php endif; ?>
                    </div>
                    <p style="text-align: center; color: #7f8c8d; margin: 15px 0;">
                        <i class="fas fa-info-circle"></i> <?php echo __('This is the current logo that will be used in labels and WO', 'osfree'); ?>
                    </p>
                </div>

                <div class="os-actions">
                    <a href="?step=1" class="os-btn os-btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo __('Previous', 'osfree'); ?>
                    </a>

                    <div>
                        <button type="button" class="os-btn" id="logo-action-btn" onclick="processLogoOption()">
                            <i class="fas fa-upload" id="btn-icon"></i>
                            <span id="btn-text">
                                <?php
                                if (!$osLogoExists) {
                                    echo __('Upload Logo', 'osfree');
                                } else {
                                    echo __('Next Step', 'osfree');
                                }
                                ?>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="step-content <?php echo ($currentStep == 3) ? 'active' : ''; ?>">
            <div class="os-card">
                <div class="os-card-header">
                    <h3><i class="fas fa-palette"></i> <?php echo __('Customize Colors', 'osfree'); ?></h3>
                </div>

                <p><?php echo __('Choose the colors that will be used in your Work Orders.', 'osfree'); ?></p>

                <div class="color-presets">
                    <div class="color-preset" onclick="applyPreset('#071A36', '#0F172A', '#071A36', '#F8FAFC')" id="preset-default">
                        <div class="preset-name"><?php echo __('Default', 'osfree'); ?></div>
                        <div class="preset-colors">
                            <div class="preset-color" style="background-color: #071A36;"></div>
                            <div class="preset-color" style="background-color: #0F172A;"></div>
                            <div class="preset-color" style="background-color: #071A36;"></div>
                            <div class="preset-color" style="background-color: #F8FAFC;"></div>
                        </div>
                    </div>

                    <div class="color-preset" onclick="applyPreset('#10B981', '#064E3B', '#6366F1', '#F0FDF4')" id="preset-green">
                        <div class="preset-name"><?php echo __('Green', 'osfree'); ?></div>
                        <div class="preset-colors">
                            <div class="preset-color" style="background-color: #10B981;"></div>
                            <div class="preset-color" style="background-color: #064E3B;"></div>
                            <div class="preset-color" style="background-color: #6366F1;"></div>
                            <div class="preset-color" style="background-color: #F0FDF4;"></div>
                        </div>
                    </div>

                    <div class="color-preset" onclick="applyPreset('#4F46E5', '#312E81', '#EC4899', '#EEF2FF')" id="preset-purple">
                        <div class="preset-name"><?php echo __('Purple', 'osfree'); ?></div>
                        <div class="preset-colors">
                            <div class="preset-color" style="background-color: #4F46E5;"></div>
                            <div class="preset-color" style="background-color: #312E81;"></div>
                            <div class="preset-color" style="background-color: #EC4899;"></div>
                            <div class="preset-color" style="background-color: #EEF2FF;"></div>
                        </div>
                    </div>

                    <div class="color-preset" onclick="applyPreset('#EF4444', '#7F1D1D', '#FB923C', '#FEF2F2')" id="preset-red">
                        <div class="preset-name"><?php echo __('Red', 'osfree'); ?></div>
                        <div class="preset-colors">
                            <div class="preset-color" style="background-color: #EF4444;"></div>
                            <div class="preset-color" style="background-color: #7F1D1D;"></div>
                            <div class="preset-color" style="background-color: #FB923C;"></div>
                            <div class="preset-color" style="background-color: #FEF2F2;"></div>
                        </div>
                    </div>
                </div>

                <form action="colors_temp.php" method="post" id="colorsForm">
                    <input type="hidden" name="_glpi_csrf_token" value="<?php echo Session::getNewCSRFToken(); ?>">
                    <input type="hidden" name="step" value="3">

                    <div class="color-picker-container">
                        <div class="color-preview" id="primary-preview" style="background-color: <?php echo $primaryColor; ?>"></div>
                        <input type="color" class="color-picker" name="primary_color" value="<?php echo $primaryColor; ?>" id="primary-color" oninput="updateColorPreview('primary')">
                        <div class="color-info">
                            <div class="color-name"><?php echo __('Primary Color', 'osfree'); ?></div>
                            <div class="color-description"><?php echo __('Used in headers and main elements', 'osfree'); ?></div>
                            <div class="color-hex" id="primary-hex"><?php echo $primaryColor; ?></div>
                        </div>
                    </div>

                    <div class="color-picker-container">
                        <div class="color-preview" id="secondary-preview" style="background-color: <?php echo $secondaryColor; ?>"></div>
                        <input type="color" class="color-picker" name="secondary_color" value="<?php echo $secondaryColor; ?>" id="secondary-color" oninput="updateColorPreview('secondary')">
                        <div class="color-info">
                            <div class="color-name"><?php echo __('Secondary Color', 'osfree'); ?></div>
                            <div class="color-description"><?php echo __('Used in texts and secondary elements', 'osfree'); ?></div>
                            <div class="color-hex" id="secondary-hex"><?php echo $secondaryColor; ?></div>
                        </div>
                    </div>

                    <div class="color-picker-container">
                        <div class="color-preview" id="accent-preview" style="background-color: <?php echo $accentColor; ?>"></div>
                        <input type="color" class="color-picker" name="accent_color" value="<?php echo $accentColor; ?>" id="accent-color" oninput="updateColorPreview('accent')">
                        <div class="color-info">
                            <div class="color-name"><?php echo __('Title Color', 'osfree'); ?></div>
                            <div class="color-description"><?php echo __('Used for company name and WO number.', 'osfree'); ?></div>
                            <div class="color-hex" id="accent-hex"><?php echo $accentColor; ?></div>
                        </div>
                    </div>

                    <div class="color-picker-container">
                        <div class="color-preview" id="light-preview" style="background-color: <?php echo $lightColor; ?>"></div>
                        <input type="color" class="color-picker" name="light_color" value="<?php echo $lightColor; ?>" id="light-color" oninput="updateColorPreview('light')">
                        <div class="color-info">
                            <div class="color-name"><?php echo __('Background Color', 'osfree'); ?></div>
                            <div class="color-description"><?php echo __('Used as section background', 'osfree'); ?></div>
                            <div class="color-hex" id="light-hex"><?php echo $lightColor; ?></div>
                        </div>
                    </div>

                    <h4 style="margin-top: 30px;"><?php echo __('Preview', 'osfree'); ?></h4>
                    <div style="background-color: #f5f5f5; padding: 15px; border-radius: 4px;">
                        <div style="background-color: white; width: 100%; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 4px; padding-bottom: 10px; position: relative; overflow: hidden;">
                            <div style="padding: 10px; background-color: white; border-bottom: 1px solid #eee;">
                                <table style="width:100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width:15%; vertical-align: middle; padding: 5px; text-align: center;">
                                            <?php if ($osLogoExists): ?>
                                                <img src="<?php echo $osLogoDisplayPath; ?>" style="height: 30px; max-width:100%;" alt="<?php echo __('Logo', 'osfree'); ?>">
                                            <?php else: ?>
                                                <div style="border: 1px dashed #ccc; width: 40px; height: 30px; display: inline-block; margin: 0 auto; text-align: center; line-height: 30px; font-size: 10px; color: #999;"><?php echo __('Logo', 'osfree'); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <td style="width:65%; vertical-align: middle; text-align: center; padding: 5px;">
                                            <div style="font-size:12px; font-weight:700; color:<?php echo $accentColor; ?>; text-transform:uppercase; margin-bottom:2px; line-height:1;" id="preview-company">
                                                <?php echo htmlspecialchars($EmpresaPlugin ?: __('Company Name', 'osfree')); ?>
                                            </div>
                                            <div style="font-size:7px; color:<?php echo $secondaryColor; ?>; line-height:1.1;">
                                                <?php echo __('EIN', 'osfree'); ?>: <?php echo htmlspecialchars($CnpjPlugin ?: '00.000.000/0000-00'); ?> · <?php echo __('Phone', 'osfree'); ?>: <?php echo htmlspecialchars($TelefonePlugin ?: '(00) 0000-0000'); ?>
                                            </div>
                                        </td>

                                        <td style="width:20%; vertical-align: middle; padding: 5px;">
                                            <table style="width:100%; border-collapse: collapse;">
                                                <tr>
                                                    <td style="width:60%; vertical-align: middle; text-align:right; padding-right:4px;">
                                                        <div style="text-align:right;">
                                                            <div style="font-size:7px; color:<?php echo $secondaryColor; ?>;"><?php echo __('WO No', 'osfree'); ?></div>
                                                            <div style="font-size:12px; font-weight:700; color:<?php echo $accentColor; ?>; line-height:1;" id="preview-os">#12345</div>
                                                            <div style="font-size:7px; color:<?php echo $secondaryColor; ?>;">DD/MM/YYYY</div>
                                                        </div>
                                                    </td>
                                                    <td style="width:40%; vertical-align: middle; text-align:center;">
                                                        <div style="border: 1px solid #ccc; width: 25px; height: 25px; display: inline-block; background-color: #f5f5f5;"></div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="padding: 0 10px;">
                                <div class="theme-preview" style="margin: 10px 0; border: none; box-shadow: none;">
                                    <div class="preview-header" id="preview-header" style="background-color: <?php echo $primaryColor; ?>; color: white;">
                                        <?php echo __('Section Header', 'osfree'); ?>
                                    </div>
                                    <div class="preview-body" id="preview-body" style="background-color: <?php echo $lightColor; ?>; border: 1px solid #ddd; border-top: none;">
                                        <div class="preview-field">
                                            <div class="preview-label" style="background-color: #F1F5F9; color: <?php echo $secondaryColor; ?>;" id="preview-label">
                                                <?php echo __('Field Label', 'osfree'); ?>
                                            </div>
                                            <div class="preview-value" style="background-color: #FFFFFF; color: <?php echo $secondaryColor; ?>;" id="preview-text">
                                                <?php echo __('Field value', 'osfree'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="os-actions">
                    <a href="?step=2" class="os-btn os-btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo __('Previous', 'osfree'); ?>
                    </a>

                    <a href="#" class="os-btn" onclick="document.getElementById('colorsForm').submit();">
                        <i class="fas fa-arrow-right"></i> <?php echo __('Next', 'osfree'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="step-content <?php echo ($currentStep == 4) ? 'active' : ''; ?>">
            <div class="os-card">
                <div class="os-card-header">
                    <h3><i class="fas fa-tag"></i> <?php echo __('Label Configuration', 'osfree'); ?></h3>
                </div>

                <p><?php echo __('Configure the size and custom text that will appear on the labels.', 'osfree'); ?></p>

                <form action="label_temp.php" method="post" id="labelForm">
                    <input type="hidden" name="_glpi_csrf_token" value="<?php echo Session::getNewCSRFToken(); ?>">
                    <input type="hidden" name="step" value="4">

                    <div style="margin-bottom: 30px;">
                        <h4><?php echo __('Label Size', 'osfree'); ?></h4>
                        <p class="description"><?php echo __('Select the label width you will use on the printer.', 'osfree'); ?></p>

                        <div class="os-form-group" style="margin-top: 20px;">
                            <label class="os-form-label"><?php echo __('Label Width', 'osfree'); ?>:</label>
                            <div style="display: flex; gap: 15px;">
                                <label style="cursor: pointer; display: flex; align-items: center;">
                                    <input type="radio" name="label_width" value="60" <?php echo ($LabelWidth == '60') ? 'checked' : ''; ?> onchange="updateLabelPreview()">
                                    <span style="margin-left: 5px;">60mm</span>
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center;">
                                    <input type="radio" name="label_width" value="70" <?php echo ($LabelWidth == '70') ? 'checked' : ''; ?> onchange="updateLabelPreview()">
                                    <span style="margin-left: 5px;">70mm</span>
                                </label>
                                <label style="cursor: pointer; display: flex; align-items: center;">
                                    <input type="radio" name="label_width" value="80" <?php echo ($LabelWidth == '80') ? 'checked' : ''; ?> onchange="updateLabelPreview()">
                                    <span style="margin-left: 5px;">80mm</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 30px;">
                        <h4><?php echo __('Custom Text', 'osfree'); ?></h4>
                        <p class="description"><?php echo __('Add custom text that will appear in the label footer (optional).', 'osfree'); ?></p>

                        <div class="os-form-group">
                            <label class="os-form-label"><?php echo __('Footer Text', 'osfree'); ?>:</label>
                            <input type="text" class="os-form-control" name="label_custom_text" value="<?php echo htmlspecialchars($LabelCustomText); ?>" maxlength="255" placeholder="<?php echo __('e.g.: Thank you for your preference!', 'osfree'); ?>" onkeyup="updateLabelPreview()">
                        </div>
                    </div>

                    <h4><?php echo __('Label Preview', 'osfree'); ?></h4>
                    <p class="description"><?php echo __('This is an approximate preview of how your label will look.', 'osfree'); ?></p>

                    <div class="label-preview-container" style="margin-top: 20px; text-align: center;">
                        <div id="label-preview" style="border: 1px dashed #ccc; display: inline-block; padding: 10px; background-color: white; max-width: 100%;">
                            <div style="text-align: center; margin-bottom: 10px;">
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($EmpresaPlugin); ?></div>
                                <div style="font-size: 12px;"><?php echo __('EIN', 'osfree'); ?>: <?php echo htmlspecialchars($CnpjPlugin); ?></div>
                                <div style="font-size: 10px;"><?php echo htmlspecialchars($EnderecoPlugin); ?>, <?php echo htmlspecialchars($CidadePlugin); ?></div>
                            </div>

                            <div style="text-align: center; margin: 10px 0;">
                                <div style="width: 60px; height: 60px; background-color: #eee; display: inline-block; margin: 0 auto; line-height: 60px; font-size: 10px; color: #999;"><?php echo __('QR Code', 'osfree'); ?></div>
                            </div>

                            <div style="text-align: center; margin-top: 5px;">
                                <div style="font-weight: bold; font-size: 16px;">#12345</div>
                                <div style="font-size: 12px;">DD/MM/YYYY</div>
                            </div>

                            <hr style="border-top: 1px solid #333; margin: 10px 0;">

                            <div style="margin-top: 10px;">
                                <div style="display: flex; margin-bottom: 5px;">
                                    <div style="width: 80px; font-weight: bold; font-size: 12px;"><?php echo __('Client', 'osfree'); ?>:</div>
                                    <div style="flex: 1; font-size: 12px;"><?php echo __('Client Name', 'osfree'); ?></div>
                                </div>
                                <div style="display: flex; margin-bottom: 5px;">
                                    <div style="width: 80px; font-weight: bold; font-size: 12px;"><?php echo __('Requester', 'osfree'); ?>:</div>
                                    <div style="flex: 1; font-size: 12px;"><?php echo __('Requester Name', 'osfree'); ?></div>
                                </div>
                                <div style="display: flex; margin-bottom: 5px;">
                                    <div style="width: 80px; font-weight: bold; font-size: 12px;"><?php echo __('Technician', 'osfree'); ?>:</div>
                                    <div style="flex: 1; font-size: 12px;"><?php echo __('Technician Name', 'osfree'); ?></div>
                                </div>
                            </div>

                            <div id="custom-text-preview" style="margin-top: 15px; text-align: center; font-style: italic; font-size: 11px; <?php echo empty($LabelCustomText) ? 'display: none;' : ''; ?>">
                                <?php echo htmlspecialchars($LabelCustomText); ?>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="os-actions">
                    <a href="?step=3" class="os-btn os-btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo __('Previous', 'osfree'); ?>
                    </a>

                    <a href="#" class="os-btn" onclick="document.getElementById('labelForm').submit();">
                        <i class="fas fa-arrow-right"></i> <?php echo __('Next', 'osfree'); ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="step-content <?php echo ($currentStep == 5) ? 'active' : ''; ?>">
            <div class="os-card">
                <div class="os-card-header">
                    <h3><i class="fas fa-check-circle"></i> <?php echo __('Finish Configuration', 'osfree'); ?></h3>
                </div>

                <div class="info-summary">
                    <h4 style="margin-top: 0; color: #2c3e50;"><?php echo __('Information Summary', 'osfree'); ?></h4>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Company Name', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($EmpresaPlugin); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('EIN', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($CnpjPlugin); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Address', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($EnderecoPlugin); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Phone', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($TelefonePlugin); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('City/State', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($CidadePlugin); ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Website', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo htmlspecialchars($SitePlugin); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><?php echo __('Colors', 'osfree'); ?>:</div>
                        <div class="info-value">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <span style="width: 20px; height: 20px; border-radius: 4px; background-color: <?php echo $primaryColor; ?>; display: inline-block; border: 1px solid #ddd;"></span>
                                <span style="width: 20px; height: 20px; border-radius: 4px; background-color: <?php echo $secondaryColor; ?>; display: inline-block; border: 1px solid #ddd;"></span>
                                <span style="width: 20px; height: 20px; border-radius: 4px; background-color: <?php echo $accentColor; ?>; display: inline-block; border: 1px solid #ddd;"></span>
                                <span style="width: 20px; height: 20px; border-radius: 4px; background-color: <?php echo $lightColor; ?>; display: inline-block; border: 1px solid #ddd;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Label Size', 'osfree'); ?>:</div>
                        <div class="info-value"><?php echo $LabelWidth; ?>mm</div>
                    </div>

                    <?php if (!empty($LabelCustomText)): ?>
                        <div class="info-item">
                            <div class="info-label"><?php echo __('Label Text', 'osfree'); ?>:</div>
                            <div class="info-value"><?php echo htmlspecialchars($LabelCustomText); ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="info-item">
                        <div class="info-label"><?php echo __('Logo', 'osfree'); ?>:</div>
                        <div class="info-value">
                            <?php if ($osLogoExists): ?>
                                <img src="<?php echo $osLogoDisplayPath; ?>" alt="<?php echo __('Logo', 'osfree'); ?>" style="max-height: 50px;">
                            <?php else: ?>
                                <span style="color: #7f8c8d;"><i class="fas fa-ban"></i> <?php echo __('No logo', 'osfree'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 style="color: #2c3e50;"><?php echo __('Settings Ready!', 'osfree'); ?></h3>
                    <p style="color: #7f8c8d;"><?php echo __('Review the information above and click "Save and Finish" to apply all settings.', 'osfree'); ?></p>
                </div>

                <div class="os-actions">
                    <a href="?step=4" class="os-btn os-btn-secondary">
                        <i class="fas fa-arrow-left"></i> <?php echo __('Previous', 'osfree'); ?>
                    </a>

                    <a href="save_all.php" class="os-btn">
                        <i class="fas fa-save"></i> <?php echo __('Save and Finish', 'osfree'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const translations = {
        'upload_logo': '<?php echo addslashes(__('Upload Logo', 'osfree')); ?>',
        'next_step': '<?php echo addslashes(__('Next Step', 'osfree')); ?>',
        'please_select_file': '<?php echo addslashes(__('Please select a file to upload.', 'osfree')); ?>',
        'logo_uploaded_success': '<?php echo addslashes(__('Logo uploaded successfully!', 'osfree')); ?>',
        'error_uploading_logo': '<?php echo addslashes(__('Error uploading logo. Please try again.', 'osfree')); ?>'
    };

    function selectLogoOption(option) {
        document.querySelectorAll('.option-card').forEach(card => {
            card.classList.remove('selected');
        });

        document.getElementById(option + '-option').classList.add('selected');

        const logoUploadSection = document.getElementById('logo-upload-section');
        const currentLogoSection = document.getElementById('current-logo-section');

        if (option === 'upload-logo') {
            logoUploadSection.style.display = 'block';
            currentLogoSection.style.display = 'none';
        } else if (option === 'keep-logo') {
            logoUploadSection.style.display = 'none';
            currentLogoSection.style.display = 'block';
        } else {
            logoUploadSection.style.display = 'none';
            currentLogoSection.style.display = 'none';
        }

        updateLogoButton();
    }

    function updateLogoButton() {
        const selected = document.querySelector('.option-card.selected');
        const btnIcon = document.getElementById('btn-icon');
        const btnText = document.getElementById('btn-text');
        if (!selected) return;

        if (selected.id === 'upload-logo-option') {
            btnIcon.className = 'fas fa-upload';
            btnText.textContent = translations.upload_logo;
        } else {
            btnIcon.className = 'fas fa-arrow-right';
            btnText.textContent = translations.next_step;
        }
    }

    function processLogoOption() {
        const selectedOption = document.querySelector('.option-card.selected').id;

        if (selectedOption === 'no-logo-option') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'no_logo.php';

            const fromWizardInput = document.createElement('input');
            fromWizardInput.type = 'hidden';
            fromWizardInput.name = 'from_wizard';
            fromWizardInput.value = '1';
            form.appendChild(fromWizardInput);

            const csrfToken = document.querySelector('input[name="_glpi_csrf_token"]').value;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_glpi_csrf_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        } else if (selectedOption === 'keep-logo-option') {
            window.location.href = '?step=3';
        } else {
            handleLogoSubmit();
        }
    }

    function handleLogoSubmit() {
        const fileInput = document.getElementById('logo-file');
        if (fileInput.files.length === 0) {
            alert(translations.please_select_file);
            return;
        }
        document.getElementById('logoForm').submit();
    }

    <?php if (isset($_GET['upload_result'])): ?>
        const uploadResult = "<?php echo $_GET['upload_result']; ?>";
        if (uploadResult === 'success') {
            alert(translations.logo_uploaded_success);
            window.location.href = '?step=3';
        } else {
            alert(translations.error_uploading_logo);
        }
    <?php endif; ?>

    function updateColorPreview(type) {
        const colorInput = document.getElementById(`${type}-color`);
        const colorPreview = document.getElementById(`${type}-preview`);
        const colorHex = document.getElementById(`${type}-hex`);
        const value = colorInput.value;

        colorPreview.style.backgroundColor = value;
        colorHex.innerText = value;

        if (type === 'primary') {
            document.getElementById('preview-header').style.backgroundColor = value;
        } else if (type === 'secondary') {
            document.getElementById('preview-label').style.color = value;
            document.getElementById('preview-text').style.color = value;

            const cnpjElement = document.querySelector('td div[style*="font-size:7px"]');
            if (cnpjElement) cnpjElement.style.color = value;
        } else if (type === 'accent') {
            document.getElementById('preview-company').style.color = value;
            document.getElementById('preview-os').style.color = value;
        } else if (type === 'light') {
            document.querySelectorAll('.preview-body').forEach(el => {
                el.style.backgroundColor = value;
            });
        }
    }

    function applyPreset(primary, secondary, accent, light) {
        document.querySelectorAll('.color-preset').forEach(preset => {
            preset.classList.remove('active');
        });

        const elementId = event.currentTarget.id;
        document.getElementById(elementId).classList.add('active');

        document.getElementById('primary-color').value = primary;
        document.getElementById('secondary-color').value = secondary;
        document.getElementById('accent-color').value = accent;
        document.getElementById('light-color').value = light;

        document.getElementById('primary-preview').style.backgroundColor = primary;
        document.getElementById('secondary-preview').style.backgroundColor = secondary;
        document.getElementById('accent-preview').style.backgroundColor = accent;
        document.getElementById('light-preview').style.backgroundColor = light;

        document.getElementById('primary-hex').innerText = primary;
        document.getElementById('secondary-hex').innerText = secondary;
        document.getElementById('accent-hex').innerText = accent;
        document.getElementById('light-hex').innerText = light;

        document.getElementById('preview-header').style.backgroundColor = primary;
        document.getElementById('preview-label').style.color = secondary;
        document.getElementById('preview-text').style.color = secondary;
        document.getElementById('preview-company').style.color = accent;
        document.getElementById('preview-os').style.color = accent;
        document.getElementById('preview-body').style.backgroundColor = light;

        const cnpjElement = document.querySelector('td div[style*="font-size:7px"]');
        if (cnpjElement) cnpjElement.style.color = secondary;
    }

    function updateLabelPreview() {
        const selectedRadio = document.querySelector('input[name="label_width"]:checked');
        if (!selectedRadio) return; 

        const labelWidth = selectedRadio.value;
        const labelPreview = document.getElementById('label-preview');

        const baseWidth = parseInt(labelWidth) * 4; 
        labelPreview.style.width = baseWidth + 'px';

        const customTextInput = document.querySelector('input[name="label_custom_text"]');
        const customTextPreview = document.getElementById('custom-text-preview');

        if (customTextInput && customTextPreview) {
            if (customTextInput.value.trim() === '') {
                customTextPreview.style.display = 'none';
            } else {
                customTextPreview.style.display = 'block';
                customTextPreview.innerText = customTextInput.value;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('label-preview')) {
            const initialWidth = '<?php echo $LabelWidth; ?>';
            const initialText = '<?php echo addslashes($LabelCustomText); ?>';

            const radioButton = document.querySelector(`input[name="label_width"][value="${initialWidth}"]`);
            if (radioButton) {
                radioButton.checked = true;
            }

            const customTextInput = document.querySelector('input[name="label_custom_text"]');
            if (customTextInput && initialText) {
                customTextInput.value = initialText;
            }

            updateLabelPreview();

            const labelPreview = document.getElementById('label-preview');
            const baseWidth = parseInt(initialWidth) * 4;
            labelPreview.style.width = baseWidth + 'px';
        }

        updateLogoButton();
    });
</script>

<?php
Html::footer();
?>
