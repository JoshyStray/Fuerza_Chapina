<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Asistencias</h1>
    <a class="btn btn-primary" href="<?= e(url('asistencia', 'crear')) ?>">Registrar asistencia</a>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="row g-2 mb-3 align-items-end">
    <input type="hidden" name="c" value="asistencia">
    <input type="hidden" name="a" value="listar">
    <div class="col-6 col-md-3 col-lg-2">
        <label for="desde" class="form-label">Desde</label>
        <input type="date" id="desde" name="desde" class="form-control" value="<?= e($desde) ?>">
    </div>
    <div class="col-6 col-md-3 col-lg-2">
        <label for="hasta" class="form-label">Hasta</label>
        <input type="date" id="hasta" name="hasta" class="form-control" value="<?= e($hasta) ?>">
    </div>
    <div class="col-12 col-md-4 col-lg-3">
        <label for="q" class="form-label">Socio</label>
        <input type="search" id="q" name="q" class="form-control" value="<?= e($busqueda) ?>" maxlength="50"
               placeholder="Nombre o código">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Filtrar</button>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Código</th>
                    <th>Socio</th>
                    <th>Registró</th>
                    <?php if (Auth::esAdmin()): ?><th class="text-end">Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$asistencias): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No hay asistencias en el periodo seleccionado.</td></tr>
            <?php endif; ?>
            <?php foreach ($asistencias as $a): ?>
                <tr>
                    <td><?= e(fecha($a['fecha'])) ?></td>
                    <td><?= e($a['hora']) ?></td>
                    <td><?= e($a['codigo']) ?></td>
                    <td><?= e($a['nombre']) ?></td>
                    <td class="small text-muted"><?= e($a['registrado_por'] ?? '—') ?></td>
                    <?php if (Auth::esAdmin()): ?>
                        <td class="text-end text-nowrap acciones">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('asistencia', 'editar', ['id' => $a['id']])) ?>">Editar</a>
                            <form method="post" action="<?= e(url('asistencia', 'eliminar')) ?>"
                                  data-confirm="¿Eliminar esta asistencia? Esta acción no se puede deshacer.">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($a['id']) ?>">
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
<p class="text-muted small mt-2"><?= e(count($asistencias)) ?> registro(s)</p>