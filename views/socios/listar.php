<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Socios</h1>
    <a class="btn btn-primary" href="<?= e(url('socio', 'crear')) ?>">Nuevo socio</a>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="row g-2 mb-3">
    <input type="hidden" name="c" value="socio">
    <input type="hidden" name="a" value="listar">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" class="form-control" name="q" value="<?= e($busqueda) ?>" maxlength="50"
               placeholder="Buscar por nombre, código o teléfono" aria-label="Buscar socios">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= e(url('socio', 'listar')) ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Registro</th>
                    <th class="text-center">Membresías</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$socios): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron socios.</td></tr>
            <?php endif; ?>
            <?php foreach ($socios as $s): ?>
                <tr>
                    <td><?= e($s['codigo']) ?></td>
                    <td><?= e($s['nombre']) ?></td>
                    <td><?= e($s['telefono'] ?? '—') ?></td>
                    <td><?= e(fecha($s['fecha_registro'])) ?></td>
                    <td class="text-center"><?= e($s['membresias']) ?></td>
                    <td>
                        <?php if ($s['activo']): ?>
                            <span class="badge text-bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap acciones">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('socio', 'editar', ['id' => $s['id']])) ?>">Editar</a>
                        <?php if (Auth::esAdmin()): ?>
                            <form method="post" action="<?= e(url('socio', 'estado')) ?>">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <?= $s['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                            <form method="post" action="<?= e(url('socio', 'eliminar')) ?>"
                                  data-confirm="<?= e('¿Eliminar a ' . $s['nombre'] . '? Solo es posible si no tiene membresías ni asistencias.') ?>">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2"><?= e(count($socios)) ?> registro(s)</p>