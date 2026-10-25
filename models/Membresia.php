<?php
declare(strict_types=1);

final class Membresia
{
    /** Listado con JOIN a socios, planes y usuarios; filtra por socio, código o plan. */
    public static function listar(string $busqueda = ''): array
    {
        $sql = "SELECT m.id, m.fecha_inicio, m.fecha_fin, m.monto_pagado,
                       s.codigo, s.nombre AS socio, p.nombre AS plan,
                       u.nombre AS registrado_por,
                       CASE
                           WHEN CURDATE() BETWEEN m.fecha_inicio AND m.fecha_fin THEN 'vigente'
                           WHEN CURDATE() < m.fecha_inicio THEN 'programada'
                           ELSE 'vencida'
                       END AS estado
                  FROM membresias m
                  JOIN socios s ON s.id = m.socio_id
                  JOIN planes p ON p.id = m.plan_id
                  LEFT JOIN usuarios u ON u.id = m.usuario_id";
        $params = [];

        if ($busqueda !== '') {
            $like = '%' . addcslashes($busqueda, '%_\\') . '%';
            $sql .= ' WHERE s.nombre LIKE :q1 OR s.codigo LIKE :q2 OR p.nombre LIKE :q3';
            $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
        }

        $stmt = Database::pdo()->prepare($sql . ' ORDER BY m.fecha_fin DESC, m.id DESC');
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT id, socio_id, plan_id, fecha_inicio, fecha_fin, monto_pagado
               FROM membresias WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function crear(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO membresias (socio_id, plan_id, usuario_id, fecha_inicio, fecha_fin, monto_pagado)
             VALUES (:socio_id, :plan_id, :usuario_id, :fecha_inicio, :fecha_fin, :monto_pagado)'
        );
        $stmt->execute([
            ':socio_id'     => $d['socio_id'],
            ':plan_id'      => $d['plan_id'],
            ':usuario_id'   => $d['usuario_id'],
            ':fecha_inicio' => $d['fecha_inicio'],
            ':fecha_fin'    => $d['fecha_fin'],
            ':monto_pagado' => $d['monto_pagado'],
        ]);

        return (int) Database::pdo()->lastInsertId();
    }

    public static function actualizar(int $id, array $d): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE membresias
                SET socio_id = :socio_id, plan_id = :plan_id, fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin, monto_pagado = :monto_pagado
              WHERE id = :id'
        );
        $stmt->execute([
            ':socio_id'     => $d['socio_id'],
            ':plan_id'      => $d['plan_id'],
            ':fecha_inicio' => $d['fecha_inicio'],
            ':fecha_fin'    => $d['fecha_fin'],
            ':monto_pagado' => $d['monto_pagado'],
            ':id'           => $id,
        ]);
    }

    public static function eliminar(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM membresias WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // ------------------------------------------------------------------
    // Reglas de negocio
    // ------------------------------------------------------------------

    /** RN-01: fecha_fin = fecha_inicio + duracion_dias del plan. */
    public static function calcularFin(string $inicio, int $duracionDias): string
    {
        return (new DateTimeImmutable($inicio))->modify("+{$duracionDias} days")->format('Y-m-d');
    }

    /**
     * RN-01 + RN-02: si el socio aún tiene una membresía vigente, la nueva
     * inicia el día siguiente a su vencimiento; si no, en la fecha solicitada.
     *
     * @return array{inicio: string, fin: string, encadenada: bool}
     */
    public static function calcularVigencia(int $socioId, int $duracionDias, string $inicioSolicitado): array
    {
        $finActual  = self::finMasReciente($socioId);
        $encadenada = $finActual !== null && $finActual >= date('Y-m-d');

        $inicio = $encadenada
            ? (new DateTimeImmutable($finActual))->modify('+1 day')->format('Y-m-d')
            : $inicioSolicitado;

        return [
            'inicio'     => $inicio,
            'fin'        => self::calcularFin($inicio, $duracionDias),
            'encadenada' => $encadenada,
        ];
    }

    private static function finMasReciente(int $socioId): ?string
    {
        $stmt = Database::pdo()->prepare('SELECT MAX(fecha_fin) FROM membresias WHERE socio_id = :socio_id');
        $stmt->execute([':socio_id' => $socioId]);

        return $stmt->fetchColumn() ?: null;
    }

    /** Cifras del panel de inicio. */
    public static function resumen(): array
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM socios WHERE activo = 1) AS socios_activos,
                    (SELECT COUNT(DISTINCT socio_id) FROM membresias
                      WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin) AS vigentes,
                    (SELECT COUNT(*) FROM membresias m
                      WHERE m.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        AND NOT EXISTS (SELECT 1 FROM membresias x
                                         WHERE x.socio_id = m.socio_id AND x.fecha_fin > m.fecha_fin)
                    ) AS por_vencer";

        return Database::pdo()->query($sql)->fetch();
    }
}