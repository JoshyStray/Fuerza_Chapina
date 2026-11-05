<div class="login-shell mx-auto">
    <section class="login-marca">
        <div>
            <p class="login-kicker mb-0">Escuintla · Guatemala</p>
            <p class="login-wordmark">
                <span>Fuerza</span>
                <span>Chapina</span>
            </p>
            <p class="login-lema">Cada socio, al día.</p>
        </div>
        <div class="banda-tejida" aria-hidden="true"></div>
    </section>

    <section class="login-form card">
        <h1 class="h4 mb-4">Iniciar sesión</h1>

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
            <div class="mb-4">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </section>
</div>