<?php
if (!defined('GLPI_ROOT')) {
    die("Access denied.");
}

Session::checkLoginUser();
Session::checkValidSessionId();

if (!Session::haveRight("config", UPDATE)) {
    Html::displayRightError();
    exit;
}

$picsDir = "../pics";


if (!is_dir($picsDir)) {
    mkdir($picsDir, 0775, true);
}

if (!isset($_FILES['arquivo'])) {
    Session::addMessageAfterRedirect("Nenhum arquivo enviado.", false, ERROR);
    Html::redirect('index.php');
}

$file = $_FILES['arquivo'];

switch ($file['error']) {
    case UPLOAD_ERR_OK:
        $arquivo_tmp = $file['tmp_name'];
        $nome = $file['name'];
        $extensao = strtolower(strrchr($nome, '.'));
        if ($extensao !== '.png') {
            Session::addMessageAfterRedirect("Apenas arquivos PNG são permitidos.", false, ERROR);
            Html::redirect('index.php');
        }

        $novoNome = "logo_os.png";
        $destino = $picsDir . '/' . $novoNome;

        if (move_uploaded_file($arquivo_tmp, $destino)) {
            Session::addMessageAfterRedirect("Logo atualizado com sucesso.", true, INFO);
        } else {
            Session::addMessageAfterRedirect("Erro ao salvar o arquivo. Verifique as permissões da pasta 'pics'.", false, ERROR);
        }
        break;

    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
        Session::addMessageAfterRedirect(
            "O arquivo enviado é muito grande. Aumente 'upload_max_filesize' e 'post_max_size' no php.ini.",
            false,
            ERROR
        );
        break;

    case UPLOAD_ERR_NO_FILE:
        Session::addMessageAfterRedirect("Nenhum arquivo enviado.", false, ERROR);
        break;

    default:
        Session::addMessageAfterRedirect("Erro ao enviar o arquivo. Código de erro: " . $file['error'], false, ERROR);
}

Html::redirect('index.php');
