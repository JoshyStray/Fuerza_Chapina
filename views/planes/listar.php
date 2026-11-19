<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Planes y precios</h1>
    <?php if (Auth::esAdmin()): ?>
        <a class="btn btn-primary" href="<?= e(url('plan', 'crear')) ?>">Nuevo plan</a>
    <?php endif; ?>
</div>
<p class="text-muted small">Recepción consulta los planes; solo el administrador los modifica.</p>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="row g-2 mb-3">
    <input type="hidden" name="c" value="plan">
    <input type="hidden" name="a" value="listar">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" class="form-control" name="q" value="<?= e($busqueda) ?>" maxlength="50"
               placeholder="Buscar por nombre de plan" aria-label="Buscar planes">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= e(url('plan', 'listar')) ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Plan</th>
                    <th class="text-center">Duración</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">Membresías</th>
                    <th>Estado</th>
                    <?php if (Auth::esAdmin()): ?><th class="text-end">Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (!$planes): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron planes.</td></tr>
            <?php endif; ?>
            <?php foreach ($planes as $p): ?>
                <tr>
                    <td><?= e($p['nombre']) ?></td>
                    <td class="text-center"><?= e($p['duracion_dias']) ?> días</td>
                    <td class="text-end"><?= e(quetzales($p['precio'])) ?></td>
                    <td class="text-center"><?= e($p['usos']) ?></td>
                    <td>
                        <?php if ($p['activo']): ?>
                            <span class="badge text-bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <?php if (Auth::esAdmin()): ?>
                        <td class="text-end text-nowrap acciones">
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(url('plan', 'editar', ['id' => $p['id']])) ?>">Editar</a>
                            <form method="post" action="<?= e(url('plan', 'estado')) ?>">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                            <form method="post" action="<?= e(url('plan', 'eliminar')) ?>"
                                  data-confirm="<?= e('¿Eliminar el plan ' . $p['nombre'] . '? Solo es posible si no tiene membresías.') ?>">
                                <?= Csrf::campo() ?>
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
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
<p class="text-muted small mt-2"><?= e(count($planes)) ?> registro(s)</p>