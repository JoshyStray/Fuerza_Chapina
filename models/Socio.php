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

    /** Listado con el número de membresías y asistencias de cada socio. */
    public static function listar(string $busqueda): array
    {
        $like = '%' . addcslashes($busqueda, '%_\\') . '%';
        $stmt = Database::pdo()->prepare(
            'SELECT s.id, s.codigo, s.nombre, s.telefono, s.fecha_registro, s.activo,
                    (SELECT COUNT(*) FROM membresias m WHERE m.socio_id = s.id) AS membresias,
                    (SELECT COUNT(*) FROM asistencias a WHERE a.socio_id = s.id) AS asistencias
               FROM socios s
              WHERE s.nombre LIKE :q1 OR s.codigo LIKE :q2 OR s.telefono LIKE :q3
              ORDER BY s.codigo'
        );
        $stmt->execute([':q1' => $like, ':q2' => $like, ':q3' => $like]);

        return $stmt->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, codigo, nombre, telefono, fecha_registro, activo FROM socios WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Crea el socio con el siguiente código libre. Reintenta si otra inserción gana la carrera. */
    public static function crear(string $nombre, ?string $telefono): string
    {
        $pdo = Database::pdo();

        for ($intento = 1; $intento <= 3; $intento++) {
            $codigo = self::siguienteCodigo();
            try {
                $pdo->prepare(
                    'INSERT INTO socios (codigo, nombre, telefono, fecha_registro)
                     VALUES (:codigo, :nombre, :telefono, CURDATE())'
                )->execute([':codigo' => $codigo, ':nombre' => $nombre, ':telefono' => $telefono]);

                return $codigo;
            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? 0) !== 1062) {
                    throw $e;
                }
            }
        }

        throw new RuntimeException('No se pudo asignar un código de socio.');
    }

    public static function actualizar(int $id, string $nombre, ?string $telefono): void
    {
        Database::pdo()->prepare('UPDATE socios SET nombre = :nombre, telefono = :telefono WHERE id = :id')
            ->execute([':nombre' => $nombre, ':telefono' => $telefono, ':id' => $id]);
    }

    public static function cambiarActivo(int $id, bool $activo): void
    {
        Database::pdo()->prepare('UPDATE socios SET activo = :activo WHERE id = :id')
            ->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
    }

    /** false si el socio tiene membresías o asistencias (RESTRICT): se desactiva en su lugar. */
    public static function eliminar(int $id): bool
    {
        try {
            $stmt = Database::pdo()->prepare('DELETE FROM socios WHERE id = :id');
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? 0) === 1451) {
                return false;
            }
            throw $e;
        }
    }

    /** Normaliza lo digitado en recepción: "1", "s1" o "S-001" se convierten en "S-001". */
    public static function normalizarCodigo(string $entrada): string
    {
        $limpio = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $entrada));

        if (preg_match('/^S?(\d{1,3})$/', $limpio, $m) === 1) {
            return 'S-' . str_pad($m[1], 3, '0', STR_PAD_LEFT);
        }

        return strtoupper(trim($entrada));
    }

    /**
     * Estado del socio para recepción: su membresía vigente o, si no tiene,
     * la más reciente. Estados: vigente, vencida, programada, sin_membresia, inactivo.
     */
    public static function consultaRecepcion(string $codigo): ?array
    {
        $sql = "SELECT s.id, s.codigo, s.nombre, s.activo,
                       m.fecha_inicio, m.fecha_fin, p.nombre AS plan,
                       DATEDIFF(m.fecha_fin, CURDATE()) AS dias_restantes,
                       (CURDATE() BETWEEN m.fecha_inicio AND m.fecha_fin) AS vigente,
                       (m.fecha_inicio > CURDATE()) AS programada
                  FROM socios s
                  LEFT JOIN membresias m ON m.id = (
                        SELECT m2.id FROM membresias m2
                         WHERE m2.socio_id = s.id
                         ORDER BY (CURDATE() BETWEEN m2.fecha_inicio AND m2.fecha_fin) DESC,
                                  m2.fecha_fin DESC, m2.id DESC
                         LIMIT 1)
                  LEFT JOIN planes p ON p.id = m.plan_id
                 WHERE s.codigo = :codigo";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        $f = $stmt->fetch();

        if (!$f) {
            return null;
        }

        $estado = match (true) {
            !$f['activo']            => 'inactivo',
            $f['fecha_fin'] === null => 'sin_membresia',
            (bool) $f['vigente']     => 'vigente',
            (bool) $f['programada']  => 'programada',
            default                  => 'vencida',
        };

        return [
            'id'             => (int) $f['id'],
            'codigo'         => $f['codigo'],
            'nombre'         => $f['nombre'],
            'plan'           => $f['plan'],
            'fecha_inicio'   => $f['fecha_inicio'],
            'fecha_fin'      => $f['fecha_fin'],
            'dias_restantes' => $f['dias_restantes'] === null ? null : (int) $f['dias_restantes'],
            'estado'         => $estado,
        ];
    }

    private static function siguienteCodigo(): string
    {
        $n = (int) Database::pdo()->query(
            'SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)), 0) + 1 FROM socios'
        )->fetchColumn();

        return 'S-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
}