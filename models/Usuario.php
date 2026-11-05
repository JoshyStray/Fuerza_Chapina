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

    public static function todos(): array
    {
        return Database::pdo()
            ->query('SELECT id, usuario, nombre, rol, activo FROM usuarios ORDER BY rol, nombre')
            ->fetchAll();
    }
}