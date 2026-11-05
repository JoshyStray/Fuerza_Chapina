<?php
declare(strict_types=1);

final class Asistencia
{
    public static function existeHoy(int $socioId): bool
    {
        $stmt = Database::pdo()->prepare(
            'SELECT 1 FROM asistencias WHERE socio_id = :socio_id AND fecha = CURDATE()'
        );
        $stmt->execute([':socio_id' => $socioId]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Inserta la asistencia de hoy. La restricción UNIQUE (socio_id, fecha) de la
     * base de datos garantiza RN-04 aun si dos recepciones registran a la vez.
     */
    public static function registrar(int $socioId, ?int $usuarioId): bool
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO asistencias (socio_id, usuario_id, fecha, fecha_hora)
             VALUES (:socio_id, :usuario_id, CURDATE(), NOW())'
        );

        try {
            $stmt->execute([':socio_id' => $socioId, ':usuario_id' => $usuarioId]);
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1062) {
                return false; // entrada duplicada: ya asistió hoy
            }
            throw $e;
        }

        return true;
    }

    /** Asistencias de hoy, la más reciente primero. */
    public static function hoy(): array
    {
        return Database::pdo()->query(
            "SELECT a.id, TIME_FORMAT(a.fecha_hora, '%H:%i') AS hora,
                    s.codigo, s.nombre, u.nombre AS registrado_por
               FROM asistencias a
               JOIN socios s ON s.id = a.socio_id
               LEFT JOIN usuarios u ON u.id = a.usuario_id
              WHERE a.fecha = CURDATE()
              ORDER BY a.fecha_hora DESC, a.id DESC"
        )->fetchAll();
    }
}