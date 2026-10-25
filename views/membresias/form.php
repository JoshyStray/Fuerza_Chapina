<?php $editando = $membresia !== null; ?>
<h1 class="h3 mb-3"><?= $editando ? 'Editar membresía' : 'Nueva membresía o renovación' ?></h1>

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

<form method="post" action="<?= e(url('membresia', $editando ? 'actualizar' : 'guardar')) ?>"
      class="card shadow-sm card-body">
    <?= Csrf::campo() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= e($membresia['id']) ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="socio_id" class="form-label">Socio</label>
            <select class="form-select" id="socio_id" name="socio_id" required>
                <option value="">Selecciona un socio…</option>
                <?php foreach ($socios as $s): ?>
                    <option value="<?= e($s['id']) ?>" <?= (string) $valores['socio_id'] === (string) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['codigo'] . ' · ' . $s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="plan_id" class="form-label">Plan</label>
            <select class="form-select" id="plan_id" name="plan_id" required>
                <option value="">Selecciona un plan…</option>
                <?php foreach ($planes as $p): ?>
                    <option value="<?= e($p['id']) ?>" data-precio="<?= e($p['precio']) ?>"
                        <?= (string) $valores['plan_id'] === (string) $p['id'] ? 'selected' : '' ?>>
                        <?= e($p['nombre'] . ' · ' . $p['duracion_dias'] . ' días · ' . quetzales($p['precio'])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12 col-md-6">
            <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio"
                   value="<?= e($valores['fecha_inicio']) ?>" required>
            <div class="form-text">
                La fecha de fin la calcula el sistema. Si el socio aún tiene una membresía vigente,
                la nueva inicia el día siguiente a su vencimiento.
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label for="monto_pagado" class="form-label">Monto pagado (Q)</label>
            <input type="number" class="form-control" id="monto_pagado" name="monto_pagado"
                   value="<?= e($valores['monto_pagado']) ?>" min="0" max="99999999.99" step="0.01" required>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Registrar membresía' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('membresia', 'listar')) ?>">Cancelar</a>
    </div>
</form>