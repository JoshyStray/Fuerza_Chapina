<?php
declare(strict_types=1);

/** Consultas de agregación del reporte (RT-09): JOIN, COUNT, SUM y GROUP BY. */
final class Reporte
{
    private const DIAS_MAX = 90;

    /** Membresías cuya última renovación vence dentro de $dias días. */
    private static function baseVencimientos(int $dias): string
    {
        $dias = max(1, min(self::DIAS_MAX, $dias));

        return "FROM membresias m
                JOIN socios s ON s.id = m.socio_id
                JOIN planes p ON p.id = m.plan_id
               WHERE m.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$dias} DAY)
                 AND NOT EXISTS (SELECT 1 FROM membresias x
                                  WHERE x.socio_id = m.socio_id AND x.fecha_fin > m.fecha_fin)";
    }

    public static function porVencer(int $dias): array
    {
        return Database::pdo()->query(
            'SELECT s.codigo, s.nombre, s.telefono, p.nombre AS plan, p.precio,
                    m.fecha_fin, DATEDIFF(m.fecha_fin, CURDATE()) AS dias_restantes '
            . self::baseVencimientos($dias) . ' ORDER BY m.fecha_fin, s.codigo'
        )->fetchAll();
    }

    /** GROUP BY plan: COUNT de renovaciones por vencer y SUM de su valor. */
    public static function resumenVencimientos(int $dias): array
    {
        return Database::pdo()->query(
            'SELECT p.nombre AS plan, COUNT(*) AS total, SUM(p.precio) AS valor '
            . self::baseVencimientos($dias) . ' GROUP BY p.id, p.nombre ORDER BY total DESC, p.nombre'
        )->fetchAll();
    }

    /** GROUP BY día de la semana: COUNT de asistencias y de socios distintos. Siempre devuelve 7 días. */
    public static function asistenciasPorDia(string $desde, string $hasta): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT DAYOFWEEK(a.fecha) AS dia, COUNT(*) AS asistencias, COUNT(DISTINCT a.socio_id) AS socios
               FROM asistencias a
              WHERE a.fecha BETWEEN :desde AND :hasta
              GROUP BY DAYOFWEEK(a.fecha)'
        );
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);

        $filas = [];
        foreach ($stmt->fetchAll() as $f) {
            $filas[(int) $f['dia']] = $f;
        }

        // DAYOFWEEK: 1 = domingo ... 7 = sábado.
        $dias = [2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles', 5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado', 1 => 'Domingo'];
        $resultado = [];

        foreach ($dias as $numero => $nombre) {
            $resultado[] = [
                'dia'         => $nombre,
                'asistencias' => (int) ($filas[$numero]['asistencias'] ?? 0),
                'socios'      => (int) ($filas[$numero]['socios'] ?? 0),
            ];
        }

        return $resultado;
    }

    /** GROUP BY plan: COUNT de renovaciones y SUM de lo pagado en el periodo. */
    public static function ingresosPorPlan(string $desde, string $hasta): array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT p.nombre AS plan, COUNT(m.id) AS renovaciones, SUM(m.monto_pagado) AS ingresos
               FROM membresias m
               JOIN planes p ON p.id = m.plan_id
              WHERE m.fecha_inicio BETWEEN :desde AND :hasta
              GROUP BY p.id, p.nombre
              ORDER BY ingresos DESC'
        );
        $stmt->execute([':desde' => $desde, ':hasta' => $hasta]);

        return $stmt->fetchAll();
    }
}