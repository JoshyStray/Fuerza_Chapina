<h1 class="h3 mb-1">Bienvenido, <?= e(Auth::usuario()['nombre']) ?></h1>
<p class="text-muted mb-4">Resumen de <?= e(APP_NAME) ?> al <?= e(date('d/m/Y')) ?>.</p>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">Socios activos</p>
                <p class="numero mb-0"><?= e($resumen['socios_activos']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen shadow-sm h-100 border-success">
            <div class="card-body">
                <p class="text-muted mb-1">Con membresía vigente</p>
                <p class="numero text-success mb-0"><?= e($resumen['vigentes']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card tarjeta-resumen shadow-sm h-100 border-warning">
            <div class="card-body">
                <p class="text-muted mb-1">Vencen en 7 días</p>
                <p class="numero text-warning mb-0"><?= e($resumen['por_vencer']) ?></p>
            </div>
        </div>
    </div>
</div>