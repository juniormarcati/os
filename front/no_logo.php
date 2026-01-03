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
Session::checkRight('config', UPDATE);

$fromWizard = isset($_POST['from_wizard']) && $_POST['from_wizard'] == '1';
$success = false;
$mensagem = "";

$storageBaseDir = rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR) . '/osfree/logo';
if (!is_dir($storageBaseDir) && !mkdir($storageBaseDir, 0755, true)) {
    $mensagem = "Não foi possível preparar o diretório de armazenamento do logotipo.";
} else {
    foreach (glob($storageBaseDir . '/logo_os.*') as $existing) {
        @unlink($existing);
    }

    $sourceFile = realpath(__DIR__ . '/../pics/empty.png');
    $targetFile = $storageBaseDir . '/logo_os.png';

    if ($sourceFile && is_file($sourceFile) && copy($sourceFile, $targetFile)) {
        $success = true;
        $mensagem = "Opção 'Sem Logotipo' aplicada com sucesso!";
        $_SESSION['os_config_temp']['logo_option'] = 'no-logo';
    } else {
        $mensagem = "Não foi possível copiar o arquivo. Verifique as permissões.";
    }
}

if ($fromWizard) {
    $result = $success ? 'success' : 'error';
    header("Location: index.php?step=3&logo_result=$result");
    exit;
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $mensagem]);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Configuração de Logotipo</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 20px;
    }
    
    .result-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    h2 {
        color: #2c3e50;
        margin-top: 0;
    }
    
    .result-message {
        margin: 20px 0;
        padding: 15px;
        border-radius: 5px;
    }
    
    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .back-button {
        display: inline-block;
        padding: 10px 20px;
        background-color: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 20px;
    }
    
    .back-button:hover {
        background-color: #2980b9;
    }
</style>
</head>

<body>
<div class="result-container">
    <h2>Configuração de Logotipo</h2>
    
    <div class="result-message <?php echo $success ? 'success' : 'error'; ?>">
        <?php echo $mensagem; ?>
    </div>
    
    <a href="index.php?step=2" class="back-button">Voltar</a>
</div>
</body>
</html>
