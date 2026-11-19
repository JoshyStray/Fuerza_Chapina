<?php $editando = $usuario !== null; ?>
<h1 class="h3 mb-3"><?= $editando ? 'Editar usuario' : 'Nuevo usuario' ?></h1>

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

<form method="post" action="<?= e(url('usuario', $editando ? 'actualizar' : 'guardar')) ?>" class="card card-body" autocomplete="off">
    <?= Csrf::campo() ?>
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= e($usuario['id']) ?>">
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="usuario" class="form-label">Usuario (inicio de sesión)</label>
            <input type="text" id="usuario" name="usuario" class="form-control"
                   value="<?= e($valores['usuario']) ?>" maxlength="30" pattern="[a-z0-9._]{3,30}"
                   <?= $editando ? 'readonly' : 'required' ?>>
            <div class="form-text">Minúsculas, números, punto o guion bajo. No se puede cambiar después.</div>
        </div>
        <div class="col-12 col-md-6">
            <label for="nombre" class="form-label">Nombre completo</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="<?= e($valores['nombre']) ?>" maxlength="100" required>
        </div>
        <div class="col-12 col-md-6">
            <label for="rol" class="form-label">Rol</label>
            <select id="rol" name="rol" class="form-select" required>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?= e($rol) ?>" <?= $valores['rol'] === $rol ? 'selected' : '' ?>><?= e(ucfirst($rol)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6">
            <label for="password" class="form-label"><?= $editando ? 'Nueva contraseña (opcional)' : 'Contraseña' ?></label>
            <input type="password" id="password" name="password" class="form-control"
                   minlength="10" maxlength="72" autocomplete="new-password" <?= $editando ? '' : 'required' ?>>
            <div class="form-text">Mínimo 10 caracteres, con letras y números.</div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editando ? 'Guardar cambios' : 'Crear usuario' ?></button>
        <a class="btn btn-outline-secondary" href="<?= e(url('usuario', 'listar')) ?>">Cancelar</a>
    </div>
</form>