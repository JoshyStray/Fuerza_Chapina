<?php
declare(strict_types=1);

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

    $c = strtolower((string) ($_GET['c'] ?? 'home'));
    $a = strtolower((string) ($_GET['a'] ?? 'index'));

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
    error_log((string) $e);
    mostrarError(500, 'Error del sistema', 'Ocurrió un problema inesperado. Inténtalo de nuevo más tarde.');
}