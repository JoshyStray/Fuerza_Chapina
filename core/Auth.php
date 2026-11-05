<?php
declare(strict_types=1);

final class Auth
{
    public static function iniciar(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'      => (int) $usuario['id'],
            'usuario' => $usuario['usuario'],
            'nombre'  => $usuario['nombre'],
            'rol'     => $usuario['rol'],
        ];
        $_SESSION['ultima_actividad'] = time();
    }

    /** false si pasó más de $maxSegundos sin actividad; si no, renueva el contador. */
    public static function sesionVigente(int $maxSegundos): bool
    {
        if (time() - ($_SESSION['ultima_actividad'] ?? 0) > $maxSegundos) {
            return false;
        }
        $_SESSION['ultima_actividad'] = time();

        return true;
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function tieneRol(string ...$roles): bool
    {
        return self::check() && in_array($_SESSION['usuario']['rol'], $roles, true);
    }

    public static function esAdmin(): bool
    {
        return self::tieneRol('administrador');
    }
}