<?php

use Glpi\Http\Response;

define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
include (GLPI_ROOT . '/inc/includes.php');

$logo_file = '../pics/logo_os.png';

if (file_exists($logo_file)) {
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($logo_file));
    readfile($logo_file);
    exit;
}

http_response_code(404);
echo "Logo not found.";
