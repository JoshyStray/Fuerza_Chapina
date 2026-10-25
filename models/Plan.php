<?php
declare(strict_types=1);

final class Plan
{
    public static function activos(): array
    {
        return Database::pdo()
            ->query('SELECT id, nombre, duracion_dias, precio FROM planes WHERE activo = 1 ORDER BY duracion_dias')
            ->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, duracion_dias, precio, activo FROM planes WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }
}