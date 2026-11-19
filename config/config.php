<?php
declare(strict_types=1);

const APP_NAME = 'Fuerza Chapina';

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'));

date_default_timezone_set('America/Guatemala');

// Carpeta de registros: se crea si falta (Git no versiona carpetas vacías).
$carpetaLogs = BASE_PATH . '/storage/logs';
if (!is_dir($carpetaLogs)) {
    @mkdir($carpetaLogs, 0775, true);
}

// Los errores técnicos nunca se muestran al usuario (RT-04 / RT-10): se registran en un archivo.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $carpetaLogs . '/app.log');
