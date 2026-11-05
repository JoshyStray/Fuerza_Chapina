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

    /** Normaliza lo digitado en recepción: "1", "s1" o "S-001" se convierten en "S-001". */
    public static function normalizarCodigo(string $entrada): string
    {
        $limpio = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $entrada));

        if (preg_match('/^S?(\d{1,3})$/', $limpio, $m)) {
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
            !$f['activo']              => 'inactivo',
            $f['fecha_fin'] === null   => 'sin_membresia',
            (bool) $f['vigente']       => 'vigente',
            (bool) $f['programada']    => 'programada',
            default                    => 'vencida',
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
}