<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    protected bool $publico = true;

    /** Hash de relleno: el tiempo de respuesta es igual exista o no el usuario. */
    private const HASH_FICTICIO = '$2y$10$rYH.DSdbtsnkHmedb7HLVOqFJphRwp3Nh3vTqTMfE9IVfyrBHYhbe';

    public function login(): void
    {
        if (Auth::check()) {
            redirect('home');
        }
        $this->mostrarLogin('', null);
    }

    public function autenticar(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();

        $usuario  = trim((string) ($_POST['usuario'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $clave    = substr($usuario, 0, 30);

        if ($clave !== '' && IntentoLogin::bloqueado($clave, $ip)) {
            http_response_code(429);
            $this->mostrarLogin($usuario, sprintf(
                'Demasiados intentos fallidos. Espera %d minutos e inténtalo de nuevo.',
                IntentoLogin::VENTANA_MINUTOS
            ));
            return;
        }

        $fila = ($usuario !== '' && strlen($usuario) <= 30) ? Usuario::buscarPorUsuario($usuario) : null;
        $hash = $fila['password_hash'] ?? self::HASH_FICTICIO;

        if (!password_verify($password, $hash) || $fila === null) {
            if ($clave !== '') {
                IntentoLogin::registrar($clave, $ip);
            }
            http_response_code(401);
            $this->mostrarLogin($usuario, 'Usuario o contraseña incorrectos.');
            return;
        }

        IntentoLogin::limpiar($clave, $ip);
        Auth::iniciar($fila);
        redirect('home');
    }

    public function logout(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();

        Auth::cerrar();
        flash('info', 'Cerraste sesión correctamente.');
        redirect('auth', 'login');
    }

    private function mostrarLogin(string $usuario, ?string $error): void
    {
        $this->render('auth/login', [
            'titulo'  => 'Iniciar sesión',
            'usuario' => $usuario,
            'error'   => $error,
        ]);
    }
}