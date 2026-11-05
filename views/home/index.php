<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Hola, <?= e(Auth::usuario()['nombre']) ?></h1>
        <p class="text-muted mb-0">Resumen al <?= e(date('d/m/Y')) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-primary" href="<?= e(url('recepcion')) ?>">Abrir recepción</a>
        <a class="btn btn-outline-primary" href="<?= e(url('membresia', 'listar')) ?>">Ver membresías</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen h-100">
            <div class="card-body">
                <p class="form-label mb-2">Socios activos</p>
                <p class="numero mb-0"><?= e($resumen['socios_activos']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen h-100 border-success">
            <div class="card-body">
                <p class="form-label mb-2">Con membresía vigente</p>
                <p class="numero text-success mb-0"><?= e($resumen['vigentes']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen h-100 border-warning">
            <div class="card-body">
                <p class="form-label mb-2">Vencen en 7 días</p>
                <p class="numero mb-0"><?= e($resumen['por_vencer']) ?></p>
            </div>
        </div>
    </div>
</div>