<?php
declare(strict_types=1);

final class Asistencia
{
    private const DUPLICADO = 1062;

    /** RN-04: ¿el socio ya tiene asistencia hoy? */
    public static function existeHoy(int $socioId): bool
    {
        return self::existeEn($socioId, date('Y-m-d'));
    }

    public static function existeEn(int $socioId, string $fecha, ?int $excluirId = null): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM asistencias WHERE socio_id = :socio AND fecha = :fecha AND id <> :id'
        );
        $stmt->execute([':socio' => $socioId, ':fecha' => $fecha, ':id' => $excluirId ?? 0]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** RN-03: ¿el socio tenía una membresía vigente en esa fecha? */
    public static function socioVigenteEn(int $socioId, string $fecha): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM membresias WHERE socio_id = :socio AND :fecha BETWEEN fecha_inicio AND fecha_fin'
        );
        $stmt->execute([':socio' => $socioId, ':fecha' => $fecha]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** Inserta una asistencia. Devuelve false si ya existe una para ese socio ese día (RN-04). */
    public static function insertar(int $socioId, ?int $usuarioId, string $fechaHora): bool
    {
        try {
            Database::pdo()->prepare(
                'INSERT INTO asistencias (socio_id, usuario_id, fecha_hora)
                 VALUES (:socio, :usuario, :fecha_hora)'
            )->execute([':socio' => $socioId, ':usuario' => $usuarioId, ':fecha_hora' => $fechaHora]);

            return true;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === self::DUPLICADO) {
                return false;
            }
            throw $e;
        }
    }

    public static function registrar(int $socioId, ?int $usuarioId): bool
    {
        return self::insertar($socioId, $usuarioId, date('Y-m-d H:i:s'));
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT id, socio_id, fecha_hora FROM asistencias WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function actualizar(int $id, int $socioId, string $fechaHora): bool
    {
        try {
            Database::pdo()->prepare(
                'UPDATE asistencias SET socio_id = :socio, fecha_hora = :fecha_hora WHERE id = :id'
            )->execute([':socio' => $socioId, ':fecha_hora' => $fechaHora, ':id' => $id]);

            return true;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === self::DUPLICADO) {
                return false;
            }
            throw $e;
        }
    }

    public static function eliminar(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM asistencias WHERE id = :id')->execute([':id' => $id]);
    }

    /** Listado filtrado por rango de fechas y búsqueda por socio. */
    public static function listar(string $desde, string $hasta, string $busqueda): array
    {
        $like = '%' . addcslashes($busqueda, '%_\\') . '%';
        $stmt = Database::pdo()->prepare(
            "SELECT a.id, a.fecha, a.fecha_hora, TIME_FORMAT(a.fecha_hora, '%H:%i') AS hora,
                    s.codigo, s.nombre, u.nombre AS registrado_por
               FROM asistencias a
               JOIN socios s ON s.id = a.socio_id
               LEFT JOIN usuarios u ON u.id = a.usuario_id
              WHERE a.fecha BETWEEN :desde AND :hasta
                AND (s.nombre LIKE :q1 OR s.codigo LIKE :q2)
              ORDER BY a.fecha_hora DESC, a.id DESC"
        );
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta, ':q1' => $like, ':q2' => $like]);

        return $stmt->fetchAll();
    }

    /** Asistencias de hoy (pantalla de recepción). */
    public static function hoy(): array
    {
        return Database::pdo()->query(
            "SELECT a.id, TIME_FORMAT(a.fecha_hora, '%H:%i') AS hora, s.codigo, s.nombre, u.nombre AS registrado_por
               FROM asistencias a
               JOIN socios s ON s.id = a.socio_id
               LEFT JOIN usuarios u ON u.id = a.usuario_id
              WHERE a.fecha = CURDATE()
              ORDER BY a.fecha_hora DESC, a.id DESC"
        )->fetchAll();
    }
}