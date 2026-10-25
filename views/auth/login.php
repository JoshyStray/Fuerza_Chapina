<div class="login-card mx-auto mt-4">
    <div class="text-center mb-4">
        <h1 class="h2 fw-bold text-warning bg-dark rounded py-3"><?= e(APP_NAME) ?></h1>
        <p class="text-muted">Sistema de membresías y asistencia</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h5 mb-3">Iniciar sesión</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('auth', 'autenticar')) ?>">
                <?= Csrf::campo() ?>
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario"
                           value="<?= e($usuario) ?>" maxlength="30" required autofocus autocomplete="username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password"
                           required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</div>