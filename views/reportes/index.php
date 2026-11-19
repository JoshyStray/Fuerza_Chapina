<?php
$totalVencen      = (int) array_sum(array_column($porPlan, 'total'));
$valorVencen      = (float) array_sum(array_column($porPlan, 'valor'));
$totalAsistencias = (int) array_sum(array_column($asistenciasDia, 'asistencias'));
$maximoDia        = max(1, ...array_column($asistenciasDia, 'asistencias'));
$ingresosTotal    = (float) array_sum(array_column($ingresos, 'ingresos'));
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-imprimir">
    <h1 class="h3 mb-0">Reportes</h1>
    <button type="button" class="btn btn-outline-primary" data-accion="imprimir">Imprimir</button>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="card card-body mb-4 no-imprimir">
    <input type="hidden" name="c" value="reporte">
    <input type="hidden" name="a" value="index">
    <div class="row g-3 align-items-end">
        <div class="col-6 col-md-3">
            <label for="dias" class="form-label">Vencimientos en</label>
            <select id="dias" name="dias" class="form-select">
                <?php foreach ($diasPermitidos as $d): ?>
                    <option value="<?= e($d) ?>" <?= $d === $dias ? 'selected' : '' ?>><?= e($d) ?> días</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="desde" class="form-label">Desde</label>
            <input type="date" id="desde" name="desde" class="form-control" value="<?= e($desde) ?>" required>
        </div>
        <div class="col-6 col-md-3">
            <label for="hasta" class="form-label">Hasta</label>
            <input type="date" id="hasta" name="hasta" class="form-control" value="<?= e($hasta) ?>" required>
        </div>
        <div class="col-6 col-md-3">
            <button type="submit" class="btn btn-dark w-100">Aplicar filtros</button>
        </div>
    </div>
</form>

<section class="mb-5">
    <h2 class="h5 mb-1">1. Membresías que vencen en los próximos <?= e($dias) ?> días</h2>
    <p class="text-muted small mb-3">Se cuenta solo la última renovación de cada socio.</p>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="card card-body tarjeta-cifra">
                <p class="form-label mb-1">Renovaciones</p>
                <p class="numero mb-0"><?= e($totalVencen) ?></p>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-body tarjeta-cifra">
                <p class="form-label mb-1">Valor estimado</p>
                <p class="numero mb-0"><?= e(quetzales($valorVencen)) ?></p>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Socio</th>
                        <th>Teléfono</th>
                        <th>Plan</th>
                        <th>Vence</th>
                        <th class="text-center">Días</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$vencen): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Ninguna membresía vence en este periodo.</td></tr>
                <?php endif; ?>
                <?php foreach ($vencen as $r): ?>
                    <tr>
                        <td><?= e($r['codigo']) ?></td>
                        <td><?= e($r['nombre']) ?></td>
                        <td><?= e($r['telefono'] ?? '—') ?></td>
                        <td><?= e($r['plan']) ?></td>
                        <td><?= e(fecha($r['fecha_fin'])) ?></td>
                        <td class="text-center"><?= e($r['dias_restantes']) ?></td>
                        <td class="text-end"><?= e(quetzales($r['precio'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <h3 class="h6">Resumen por plan (GROUP BY)</h3>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Plan</th>
                        <th class="text-center">Renovaciones (COUNT)</th>
                        <th class="text-end">Valor (SUM)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($porPlan as $p): ?>
                    <tr>
                        <td><?= e($p['plan']) ?></td>
                        <td class="text-center"><?= e($p['total']) ?></td>
                        <td class="text-end"><?= e(quetzales($p['valor'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="fc-total">
                        <td>Total</td>
                        <td class="text-center"><?= e($totalVencen) ?></td>
                        <td class="text-end"><?= e(quetzales($valorVencen)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="mb-5">
    <h2 class="h5 mb-1">2. Asistencias por día de la semana</h2>
    <p class="text-muted small mb-3">
        Del <?= e(fecha($desde)) ?> al <?= e(fecha($hasta)) ?>. Total: <?= e($totalAsistencias) ?> asistencias.
    </p>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Día</th>
                        <th class="text-center">Asistencias (COUNT)</th>
                        <th class="text-center">Socios distintos (COUNT DISTINCT)</th>
                        <th>Proporción</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($asistenciasDia as $d): ?>
                    <tr>
                        <td><?= e($d['dia']) ?></td>
                        <td class="text-center"><?= e($d['asistencias']) ?></td>
                        <td class="text-center"><?= e($d['socios']) ?></td>
                        <td>
                            <progress class="fc-progreso" value="<?= e($d['asistencias']) ?>"
                                      max="<?= e($maximoDia) ?>" aria-label="Proporción de <?= e($d['dia']) ?>"></progress>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section>
    <h2 class="h5 mb-1">3. Ingresos por plan en el periodo</h2>
    <p class="text-muted small mb-3">Según la fecha de inicio de cada membresía registrada en el periodo.</p>
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Plan</th>
                        <th class="text-center">Renovaciones (COUNT)</th>
                        <th class="text-end">Ingresos (SUM)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$ingresos): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Sin membresías registradas en el periodo.</td></tr>
                <?php endif; ?>
                <?php foreach ($ingresos as $i): ?>
                    <tr>
                        <td><?= e($i['plan']) ?></td>
                        <td class="text-center"><?= e($i['renovaciones']) ?></td>
                        <td class="text-end"><?= e(quetzales($i['ingresos'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="fc-total">
                        <td>Total</td>
                        <td class="text-center"><?= e(array_sum(array_column($ingresos, 'renovaciones'))) ?></td>
                        <td class="text-end"><?= e(quetzales($ingresosTotal)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>