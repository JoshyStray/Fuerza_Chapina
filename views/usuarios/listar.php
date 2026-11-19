<?php $yo = Auth::id(); ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Usuarios del sistema</h1>
    <a class="btn btn-primary" href="<?= e(url('usuario', 'crear')) ?>">Nuevo usuario</a>
</div>

<form method="get" action="<?= e(BASE_URL . '/index.php') ?>" class="row g-2 mb-3">
    <input type="hidden" name="c" value="usuario">
    <input type="hidden" name="a" value="listar">
    <div class="col-12 col-md-6 col-lg-4">
        <input type="search" class="form-control" name="q" value="<?= e($busqueda) ?>" maxlength="50"
               placeholder="Buscar por usuario o nombre" aria-label="Buscar usuarios">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a class="btn btn-outline-secondary" href="<?= e(url('usuario', 'listar')) ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-center">Registros</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <?php $esYo = (int) $u['id'] === $yo; ?>
                <tr>
                    <td>
                        <?= e($u['usuario']) ?>
                        <?php if ($esYo): ?><span class="badge text-bg-warning ms-1">Tú</span><?php endif; ?>
                    </td>
                    <td><?= e($u['nombre']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= e(ucfirst($u['rol'])) ?></span></td>
                    <td>
                        <?php if ($u['activo']): ?>
                            <span class="badge text-bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge text-bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= e($u['registros']) ?></td>
                    <td class="text-end text-nowrap acciones">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('usuario', 'editar', ['id' => $u['id']])) ?>">Editar</a>
                        <form method="post" action="<?= e(url('usuario', 'estado')) ?>">
                            <?= Csrf::campo() ?>
                            <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" <?= $esYo ? 'disabled' : '' ?>>
                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>
                        <form method="post" action="<?= e(url('usuario', 'eliminar')) ?>"
                              data-confirm="<?= e('¿Eliminar el usuario ' . $u['usuario'] . '? Esta acción no se puede deshacer.') ?>">
                            <?= Csrf::campo() ?>
                            <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    <?= ($esYo || (int) $u['registros'] > 0) ? 'disabled' : '' ?>>Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<p class="text-muted small mt-2">
    Los usuarios con membresías o asistencias registradas no se pueden eliminar: desactívalos para conservar el historial.
</p>