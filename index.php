<?php
declare(strict_types=1);

// Requisitos mínimos. Se revisan ANTES de cargar el resto de archivos: con display_errors apagado,
// un error de compilación (por ejemplo, un PHP antiguo) dejaría la página en blanco sin explicación.
if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Requisito de PHP</title></head>'
        . '<body style="font-family:sans-serif;max-width:40rem;margin:3rem auto;padding:0 1rem">'
        . '<h1>Versión de PHP no compatible</h1>'
        . '<p>Fuerza Chapina necesita <strong>PHP 8.0 o superior</strong>. Actualiza XAMPP '
        . '(o cambia la versión de PHP desde el panel de XAMPP) y reinicia Apache.</p>'
        . '</body></html>';
    exit;
}

$faltantes = array_filter(
    ['pdo_mysql', 'mbstring'],
    static fn (string $extension): bool => !extension_loaded($extension)
);

if ($faltantes !== []) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    $lista = implode(', ', array_map(static fn (string $e): string => "extension={$e}", $faltantes));
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Extensiones de PHP</title></head>'
        . '<body style="font-family:sans-serif;max-width:40rem;margin:3rem auto;padding:0 1rem">'
        . '<h1>Falta una extensión de PHP</h1>'
        . '<p>En XAMPP abre <strong>Config → PHP (php.ini)</strong>, quita el punto y coma delante de: '
        . '<code>' . htmlspecialchars($lista, ENT_QUOTES, 'UTF-8') . '</code>, guarda el archivo y reinicia Apache.</p>'
        . '</body></html>';
    exit;
}

// Front controller: todas las peticiones entran por aquí (?c=controlador&a=accion).
require __DIR__ . '/config/config.php';
require BASE_PATH . '/core/helpers.php';

spl_autoload_register(static function (string $clase): void {
    foreach (['core', 'models', 'controllers'] as $carpeta) {
        $archivo = BASE_PATH . "/{$carpeta}/{$clase}.php";
        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

// Cabeceras de seguridad (RT-10): sin recursos externos, sin marcos, sin caché de páginas privadas.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; script-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
header('Cache-Control: no-store');

// Sesión endurecida: solo cookies, ID estricto, HttpOnly y SameSite.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('FCSESSID');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']),
]);
session_start();

const SESION_INACTIVIDAD_MAX = 1800; // 30 minutos

try {
    if (Auth::check() && !Auth::sesionVigente(SESION_INACTIVIDAD_MAX)) {
        Auth::cerrar();
        flash('warning', 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.');
    }

    $c = is_string($_GET['c'] ?? null) ? strtolower($_GET['c']) : 'home';
    $a = is_string($_GET['a'] ?? null) ? strtolower($_GET['a']) : 'index';

    $clase = ucfirst($c) . 'Controller';

    if (!preg_match('/^[a-z]+$/', $c) || !preg_match('/^[a-z]+$/', $a) || !class_exists($clase)) {
        mostrarError(404, 'Página no encontrada', 'La dirección solicitada no existe.');
    }

    $controlador = new $clase();

    // is_callable solo acepta métodos públicos: los métodos protegidos del núcleo no son accesibles por URL.
    if (!is_callable([$controlador, $a])) {
        mostrarError(404, 'Página no encontrada', 'La dirección solicitada no existe.');
    }

    $controlador->$a();
} catch (Throwable $e) {
    error_log((string) $e); // el detalle técnico queda en storage/logs/app.log, nunca en pantalla
    if ($e instanceof PDOException) {
        mostrarError(500, 'Base de datos no disponible',
            'No se pudo usar la base de datos. Verifica que MySQL esté iniciado en XAMPP y que database.sql se haya importado completo.');
    }
    mostrarError(500, 'Error del sistema', 'Ocurrió un problema inesperado. Inténtalo de nuevo más tarde.');
}
