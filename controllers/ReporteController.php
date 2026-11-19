<?php
declare(strict_types=1);

final class ReporteController extends Controller
{
    private const DIAS_PERMITIDOS = [7, 15, 30];

    /** Consulta para ambos roles (propuesta §3). Todo filtro se valida antes de usarse. */
    public function index(): void
    {
        $dias = (int) ($_GET['dias'] ?? 7);
        if (!in_array($dias, self::DIAS_PERMITIDOS, true)) {
            $dias = 7;
        }

        $desde = Validar::fechaOpcional($_GET['desde'] ?? null, date('Y-m-d', strtotime('-30 days')));
        $hasta = Validar::fechaOpcional($_GET['hasta'] ?? null, date('Y-m-d'));

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $this->render('reportes/index', [
            'titulo'         => 'Reportes',
            'dias'           => $dias,
            'diasPermitidos' => self::DIAS_PERMITIDOS,
            'desde'          => $desde,
            'hasta'          => $hasta,
            'vencen'         => Reporte::porVencer($dias),
            'porPlan'        => Reporte::resumenVencimientos($dias),
            'asistenciasDia' => Reporte::asistenciasPorDia($desde, $hasta),
            'ingresos'       => Reporte::ingresosPorPlan($desde, $hasta),
        ]);
    }
}