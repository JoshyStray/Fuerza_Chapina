<?php
declare(strict_types=1);

final class Socio
{
    public static function activos(): array
    {
        return Database::pdo()
            ->query('SELECT id, codigo, nombre FROM socios WHERE activo = 1 ORDER BY nombre')
            ->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, codigo, nombre, telefono, fecha_registro, activo FROM socios WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }
}