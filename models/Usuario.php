<?php
declare(strict_types=1);

final class Usuario
{
    public static function buscarPorUsuario(string $usuario): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, usuario, password_hash, nombre, rol
               FROM usuarios
              WHERE usuario = :usuario AND activo = 1'
        );
        $stmt->execute([':usuario' => $usuario]);

        return $stmt->fetch() ?: null;
    }
}