<?php
declare(strict_types=1);

final class Plan
{
    /** Solo planes activos: son los que se ofrecen al registrar una membresía. */
    public static function activos(): array
    {
        return Database::pdo()
            ->query('SELECT id, nombre, duracion_dias, precio FROM planes WHERE activo = 1 ORDER BY duracion_dias')
            ->fetchAll();
    }

    /** Listado completo con el número de membresías que usan cada plan. */
    public static function listar(string $busqueda): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.id, p.nombre, p.duracion_dias, p.precio, p.activo,
                    (SELECT COUNT(*) FROM membresias m WHERE m.plan_id = p.id) AS usos
               FROM planes p
              WHERE p.nombre LIKE :q
              ORDER BY p.duracion_dias'
        );
        $stmt->execute([':q' => '%' . addcslashes($busqueda, '%_\\') . '%']);

        return $stmt->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, nombre, duracion_dias, precio, activo FROM planes WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM planes WHERE nombre = :nombre AND id <> :id');
        $stmt->execute([':nombre' => $nombre, ':id' => $excluirId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function crear(string $nombre, int $dias, string $precio): void
    {
        Database::pdo()->prepare(
            'INSERT INTO planes (nombre, duracion_dias, precio) VALUES (:nombre, :dias, :precio)'
        )->execute([':nombre' => $nombre, ':dias' => $dias, ':precio' => $precio]);
    }

    /** Cambiar el precio no altera membresías ya registradas: cada una guarda su monto_pagado. */
    public static function actualizar(int $id, string $nombre, int $dias, string $precio): void
    {
        Database::pdo()->prepare(
            'UPDATE planes SET nombre = :nombre, duracion_dias = :dias, precio = :precio WHERE id = :id'
        )->execute([':nombre' => $nombre, ':dias' => $dias, ':precio' => $precio, ':id' => $id]);
    }

    public static function cambiarActivo(int $id, bool $activo): void
    {
        Database::pdo()->prepare('UPDATE planes SET activo = :activo WHERE id = :id')
            ->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
    }

    /** false si el plan tiene membresías (RESTRICT): se desactiva en su lugar. */
    public static function eliminar(int $id): bool
    {
        try {
            $stmt = Database::pdo()->prepare('DELETE FROM planes WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1451) {
                return false;
            }
            throw $e;
        }
    }
}