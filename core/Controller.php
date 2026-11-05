<?php
declare(strict_types=1);

abstract class Controller
{
    /** Los controladores públicos (p. ej. login) no exigen sesión. */
    protected bool $publico = false;

    public function __construct()
    {
        if (!$this->publico) {
            $this->requerirSesion();
        }
    }

    protected function render(string $vista, array $datos = []): void
    {
        renderizar($vista, $datos);
    }

    protected function requerirSesion(): void
    {
        if (!Auth::check()) {
            if (esAjax()) {
                mostrarError(401, 'Sesión expirada', 'Tu sesión expiró. Inicia sesión de nuevo.');
            }
            flash('warning', 'Inicia sesión para continuar.');
            redirect('auth', 'login');
        }
    }

    /** Verifica el rol en el servidor: ocultar botones no basta (RT-07). */
    protected function requerirRol(string ...$roles): void
    {
        if (!Auth::tieneRol(...$roles)) {
            mostrarError(403, 'Acceso denegado', 'No tienes permiso para realizar esta acción.');
        }
    }

    protected function requerirPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            mostrarError(405, 'Método no permitido', 'Esta acción solo acepta envíos de formulario.');
        }
    }

    protected function verificarCsrf(): void
    {
        // Los formularios envían el token en el cuerpo; fetch() lo envía en la cabecera X-CSRF-Token.
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!Csrf::validar($token)) {
            mostrarError(403, 'Solicitud no válida', 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.');
        }
    }
}