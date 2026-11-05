<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (Auth::check()): ?>
        <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <?php endif; ?>
    <title><?= e(isset($titulo) ? $titulo . ' · ' . APP_NAME : APP_NAME) ?></title>
    <link href="<?= e(asset('vendor/bootstrap/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('css/app.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<?php if (Auth::check()) { require __DIR__ . '/menu.php'; } ?>
<main class="container py-4 flex-grow-1">
    <?php foreach (consumirFlash() as $aviso): ?>
        <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($aviso['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endforeach; ?>