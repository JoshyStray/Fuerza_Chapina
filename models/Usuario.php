<?php
declare(strict_types=1);

final class Usuario
{
    public const ROLES = ['administrador', 'recepcionista'];

    /** Solo los usuarios activos pueden iniciar sesión. */
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

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, usuario, nombre, rol, activo FROM usuarios WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Listado con el número de registros (membresías y asistencias) de cada usuario. */
    public static function listar(string $busqueda): array
    {
        $like = '%' . addcslashes($busqueda, '%_\\') . '%';
        $stmt = Database::pdo()->prepare(
            'SELECT u.id, u.usuario, u.nombre, u.rol, u.activo,
                    (SELECT COUNT(*) FROM membresias m WHERE m.usuario_id = u.id)
                  + (SELECT COUNT(*) FROM asistencias a WHERE a.usuario_id = u.id) AS registros
               FROM usuarios u
              WHERE u.usuario LIKE :q1 OR u.nombre LIKE :q2
              ORDER BY u.rol, u.nombre'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like]);

        return $stmt->fetchAll();
    }

    public static function existeUsuario(string $usuario, ?int $excluirId = null): bool
    {
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario AND id <> :id');
        $stmt->execute([':usuario' => $usuario, ':id' => $excluirId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function crear(string $usuario, string $nombre, string $rol, string $passwordHash): int
    {
        $pdo = Database::pdo();
        $pdo->prepare(
            'INSERT INTO usuarios (usuario, password_hash, nombre, rol) VALUES (:usuario, :hash, :nombre, :rol)'
        )->execute([':usuario' => $usuario, ':hash' => $passwordHash, ':nombre' => $nombre, ':rol' => $rol]);

        return (int) $pdo->lastInsertId();
    }

    /** La contraseña solo cambia si llega un hash nuevo. */
    public static function actualizar(int $id, string $nombre, string $rol, ?string $passwordHash): void
    {
        $sql = 'UPDATE usuarios SET nombre = :nombre, rol = :rol';
        $params = [':nombre' => $nombre, ':rol' => $rol, ':id' => $id];

        if ($passwordHash !== null) {
            $sql .= ', password_hash = :hash';
            $params[':hash'] = $passwordHash;
        }

        Database::pdo()->prepare($sql . ' WHERE id = :id')->execute($params);
    }

    public static function cambiarActivo(int $id, bool $activo): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id')
            ->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
    }

    public static function registros(int $id): int
    {
        $stmt = Database::pdo()->prepare(
            'SELECT (SELECT COUNT(*) FROM membresias WHERE usuario_id = :id1)
                  + (SELECT COUNT(*) FROM asistencias WHERE usuario_id = :id2)'
        );
        $stmt->execute([':id1' => $id, ':id2' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public static function eliminar(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM usuarios WHERE id = :id')->execute([':id' => $id]);
    }

    public static function administradoresActivos(): int
    {
        return (int) Database::pdo()
            ->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'administrador' AND activo = 1")
            ->fetchColumn();
    }
}