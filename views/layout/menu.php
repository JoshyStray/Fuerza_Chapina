<?php
// Opciones del menú según el rol. Cada módulo nuevo se agrega aquí.
$menuOpciones = [
    ['etiqueta' => 'Inicio',     'c' => 'home',      'a' => 'index',  'roles' => ['administrador', 'recepcionista']],
    ['etiqueta' => 'Membresías', 'c' => 'membresia', 'a' => 'listar', 'roles' => ['administrador', 'recepcionista']],
];
$menuActual  = $_GET['c'] ?? 'home';
$menuUsuario = Auth::usuario();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="<?= e(url('home')) ?>"><?= e(APP_NAME) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Mostrar menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">
                <?php foreach ($menuOpciones as $opcion): ?>
                    <?php if (Auth::tieneRol(...$opcion['roles'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $menuActual === $opcion['c'] ? 'active' : '' ?>"
                               href="<?= e(url($opcion['c'], $opcion['a'])) ?>"><?= e($opcion['etiqueta']) ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <form method="post" action="<?= e(url('auth', 'logout')) ?>" class="d-flex align-items-center gap-2">
                <?= Csrf::campo() ?>
                <span class="navbar-text small">
                    <?= e($menuUsuario['nombre']) ?> <span class="badge text-bg-secondary"><?= e($menuUsuario['rol']) ?></span>
                </span>
                <button type="submit" class="btn btn-outline-light btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>