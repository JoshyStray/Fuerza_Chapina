<?php
declare(strict_types=1);

// Conexión de la aplicación. Usa el usuario fc_app (privilegios mínimos, creado por
// database.sql), nunca root. Las credenciales viven aquí y nunca en las vistas (RT-10).
// Son credenciales de laboratorio: en un servidor real se cargarían desde variables de entorno.
return [
    'host'    => '127.0.0.1',
    'port'    => 3306,
    'dbname'  => 'fuerza_chapina',
    'user'    => 'fc_app',
    'pass'    => 'FcApp2026!',
    'charset' => 'utf8mb4',
];