<?php
$colores = ['vigente' => 'success', 'programada' => 'info', 'vencida' => 'danger'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Membresías</h1>
    <a class="btn btn-primary" href="<?= e(url('membresia', 'crear')) ?>">Nueva membresía</a>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="row g-2 mb-3">
    <input type="hidden" name="c" value="membresia">
    <input type="hidden" name="a" value="listar">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" class="form-control" name="q" value="<?= e($busqueda) ?>" maxlength="50"
               placeholder="Buscar por socio, código o plan" aria-label="Buscar membresías">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= e(url('membresia', 'listar')) ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Socio</th>
                    <th>Plan</th>
                    <th>Inicio</th>
                    <th>Vence</th>
                    <th class="text-end">Monto</th>
                    <th>Estado</th>
                    <th>Registró</th>
                    <?php if (Auth::esAdmin()): ?><th class="text-end">Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$membresias): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No se encontraron membresías.</td></tr>
            <?php endif; ?>
            <?php foreach ($membresias as $m): ?>
                <tr>
                    <td><?= e($m['codigo']) ?></td>
                    <td><?= e($m['socio']) ?></td>
                    <td><?= e($m['plan']) ?></td>
                    <td><?= e(fecha($m['fecha_inicio'])) ?></td>
                    <td><?= e(fecha($m['fecha_fin'])) ?></td>
                    <td class="text-end"><?= e(quetzales($m['monto_pagado'])) ?></td>
                    <td><span class="badge text-bg-<?= e($colores[$m['estado']]) ?>"><?= e(ucfirst($m['estado'])) ?></span></td>
                    <td class="small text-muted"><?= e($m['registrado_por'] ?? '—') ?></td>
                    <?php if (Auth::esAdmin()): ?>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= e(url('membresia', 'editar', ['id' => $m['id']])) ?>">Editar</a>
                            <form method="post" action="<?= e(url('membresia', 'eliminar')) ?>" class="d-inline"
                                  data-confirm="<?= e('¿Eliminar la membresía de ' . $m['socio'] . '? Esta acción no se puede deshacer.') ?>">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($m['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><?= e(count($membresias)) ?> registro(s)</p>