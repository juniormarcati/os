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
$uploadSuccess = false;
$mensagem = "";
$storedPath = '';

$storageBaseDir = rtrim(GLPI_PLUGIN_DOC_DIR, DIRECTORY_SEPARATOR) . '/osfree/logo';

if (!is_dir($storageBaseDir) && !mkdir($storageBaseDir, 0755, true)) {
    $mensagem = "Não foi possível preparar o diretório de armazenamento do logotipo.";
} elseif(isset($_FILES['arquivo']['name']) && $_FILES["arquivo"]["error"] == 0) {
    $arquivo_tmp = $_FILES['arquivo']['tmp_name'];
    $nome = $_FILES['arquivo']['name'];
    $extensao = strtolower(strrchr($nome, '.'));
    
    if($extensao && strstr('.jpg;.jpeg;.gif;.png', $extensao)) {
        foreach (glob($storageBaseDir . '/logo_os.*') as $existing) {
            @unlink($existing);
        }

        $novoNome = 'logo_os' . $extensao;
        $destino = $storageBaseDir . '/' . $novoNome;
        
        if(@move_uploaded_file($arquivo_tmp, $destino)) {
            $uploadSuccess = true;
            $storedPath = $destino;
            $mensagem = "Arquivo enviado com sucesso!";
        } else {
            $mensagem = "Erro ao salvar o arquivo. Aparentemente você não tem permissão de escrita.";
        }
    } else {
        $mensagem = "Você poderá enviar apenas arquivos \"*.jpg;*.jpeg;*.gif;*.png\"";
    }
} else {
    if ($mensagem === "") {
        $mensagem = "Você não enviou nenhum arquivo!";
    }
}

if ($fromWizard) {
    $result = $uploadSuccess ? 'success' : 'error';
    header("Location: index.php?step=3&upload_result=$result");
    exit;
}

function osfreeBuildDataUriLocal(?string $path): string
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Upload de Logotipo</title>
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
    
    .logo-preview {
        text-align: center;
        margin: 20px 0;
    }
    
    .logo-preview img {
        max-width: 100%;
        max-height: 150px;
        border: 1px solid #eee;
        padding: 10px;
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
    
    .file-details {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>
</head>

<body>
<div class="result-container">
    <h2>Resultado do Upload</h2>
    
    <div class="result-message <?php echo $uploadSuccess ? 'success' : 'error'; ?>">
        <?php echo $mensagem; ?>
    </div>
    
    <?php if ($uploadSuccess): ?>
        <div class="file-details">
            <p>Arquivo enviado: <strong><?php echo htmlspecialchars($_FILES['arquivo']['name']); ?></strong></p>
            <p>Tipo: <strong><?php echo htmlspecialchars($_FILES['arquivo']['type']); ?></strong></p>
            <p>Tamanho: <strong><?php echo htmlspecialchars($_FILES['arquivo']['size']); ?> bytes</strong></p>
            <p>Salvo como: <strong><?php echo htmlspecialchars($storedPath); ?></strong></p>
        </div>
        
        <div class="logo-preview">
            <img src="<?php echo osfreeBuildDataUriLocal($storedPath); ?>" alt="Logotipo enviado">
        </div>
    <?php endif; ?>
    
    <a href="index.php?step=2" class="back-button">Voltar</a>
</div>
</body>
</html>
