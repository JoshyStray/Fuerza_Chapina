<?php $editando = $asistencia !== null; ?>
<h1 class="h3 mb-3"><?= $editando ? 'Editar asistencia' : 'Registrar asistencia' ?></h1>

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

<form method="post" action="<?= e(url('asistencia', $editando ? 'actualizar' : 'guardar')) ?>" class="card card-body">
    <?= Csrf::campo() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= e($asistencia['id']) ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="socio_id" class="form-label">Socio</label>
            <select id="socio_id" name="socio_id" class="form-select" required>
                <option value="">Selecciona un socio…</option>
                <?php foreach ($socios as $s): ?>
                    <option value="<?= e($s['id']) ?>" <?= (string) $valores['socio_id'] === (string) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['codigo'] . ' · ' . $s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label for="fecha_hora" class="form-label">Fecha y hora</label>
            <input type="datetime-local" id="fecha_hora" name="fecha_hora" class="form-control"
                   value="<?= e($valores['fecha_hora']) ?>" max="<?= e($maximo) ?>" required>
        </div>
    </div>
    <p class="form-text mt-3">
        Se valida que el socio tuviera membresía vigente en esa fecha (RN-03) y que no tenga otra asistencia ese día (RN-04).
    </p>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Registrar' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('asistencia', 'listar')) ?>">Cancelar</a>
    </div>
</form>