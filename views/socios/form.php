<?php $editando = $socio !== null; ?>
<h1 class="h3 mb-3"><?= $editando ? 'Editar socio' : 'Nuevo socio' ?></h1>

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

<form method="post" action="<?= e(url('socio', $editando ? 'actualizar' : 'guardar')) ?>" class="card card-body">
    <?= Csrf::campo() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= e($socio['id']) ?>">
    <?php endif; ?>

    <div class="row g-3">
        <?php if ($editando): ?>
            <div class="col-12 col-md-4">
                <label class="form-label">Código</label>
                <input type="text" class="form-control" value="<?= e($socio['codigo']) ?>" readonly>
            </div>
        <?php endif; ?>
        <div class="col-12 <?= $editando ? 'col-md-8' : 'col-md-12' ?>">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="<?= e($valores['nombre']) ?>" maxlength="100" required>
        </div>
        <div class="col-12 col-md-6">
            <label for="telefono" class="form-label">Teléfono (opcional)</label>
            <input type="tel" id="telefono" name="telefono" class="form-control"
                   value="<?= e($valores['telefono']) ?>" maxlength="15" pattern="[0-9 +\-]{8,15}"
                   placeholder="5555-1234">
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Registrar socio' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('socio', 'listar')) ?>">Cancelar</a>
    </div>
</form>