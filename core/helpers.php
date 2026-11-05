<?php
declare(strict_types=1);

/** Escapa una salida para HTML (defensa contra XSS). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $controlador = 'home', string $accion = 'index', array $params = []): string
{
    return BASE_URL . '/index.php?' . http_build_query(['c' => $controlador, 'a' => $accion] + $params);
}

function asset(string $ruta): string
{
    return BASE_URL . '/public/' . ltrim($ruta, '/');
}

function redirect(string $controlador = 'home', string $accion = 'index', array $params = []): never
{
    header('Location: ' . url($controlador, $accion, $params));
    exit;
}

function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function consumirFlash(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

function fecha(?string $fecha): string
{
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '';
}

function quetzales(float|int|string $monto): string
{
    return 'Q ' . number_format((float) $monto, 2);
}

/** Dibuja una vista dentro del layout (encabezado + menú + pie). */
function renderizar(string $vista, array $datos = []): void
{
    extract($datos, EXTR_SKIP);
    require BASE_PATH . '/views/layout/header.php';
    require BASE_PATH . "/views/{$vista}.php";
    require BASE_PATH . '/views/layout/footer.php';
}

/** Las peticiones hechas con fetch() envían esta cabecera y esperan JSON, no HTML. */
function esAjax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

function responderJson(array $datos, int $codigo = 200): never
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    exit;
}

function mostrarError(int $codigo, string $titulo, string $mensaje): never
{
    if (esAjax()) {
        responderJson(['ok' => false, 'mensaje' => $mensaje], $codigo);
    }

    http_response_code($codigo);
    renderizar('errors/error', [
        'titulo'  => $titulo,
        'codigo'  => $codigo,
        'mensaje' => $mensaje,
    ]);
    exit;
}