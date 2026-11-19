<?php $editando = $plan !== null; ?>
<h1 class="h3 mb-3"><?= $editando ? 'Editar plan' : 'Nuevo plan' ?></h1>

<?php if ($errores): ?>
    <div class="alert alert-danger" role="alert">
        <p class="mb-1 fw-bold">Revisa los siguientes datos:</p>
        <ul class="mb-0">
            <?php foreach ($errores as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= e(url('plan', $editando ? 'actualizar' : 'guardar')) ?>" class="card card-body">
    <?= Csrf::campo() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= e($plan['id']) ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="nombre" class="form-label">Nombre del plan</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="<?= e($valores['nombre']) ?>" maxlength="50" required>
        </div>
        <div class="col-6 col-md-3">
            <label for="duracion_dias" class="form-label">Duración (días)</label>
            <input type="number" id="duracion_dias" name="duracion_dias" class="form-control"
                   value="<?= e($valores['duracion_dias']) ?>" min="1" max="3650" step="1" required>
        </div>
        <div class="col-6 col-md-3">
            <label for="precio" class="form-label">Precio (Q)</label>
            <input type="number" id="precio" name="precio" class="form-control"
                   value="<?= e($valores['precio']) ?>" min="0.01" max="99999999.99" step="0.01" required>
        </div>
    </div>
    <p class="form-text mt-3">Un cambio de precio no modifica las membresías ya registradas: cada una conserva el monto que se pagó.</p>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Crear plan' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('plan', 'listar')) ?>">Cancelar</a>
    </div>
</form>