<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4">
    <h1 class="h3 mb-0">Recepción</h1>
    <p class="text-muted mb-0"><?= e(date('d/m/Y')) ?></p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card card-body">
            <label for="codigo" class="form-label">Código del socio</label>
            <input type="text" id="codigo" class="form-control form-control-lg campo-codigo"
                   maxlength="10" autocomplete="off" spellcheck="false" autofocus
                   placeholder="S-001"
                   data-url-consultar="<?= e(url('recepcion', 'consultar')) ?>"
                   data-url-registrar="<?= e(url('recepcion', 'registrar')) ?>">

            <div id="panel-socio" class="semaforo semaforo--espera mt-4" aria-live="polite">
                <div class="semaforo__luz" aria-hidden="true"></div>
                <div class="semaforo__cuerpo">
                    <p class="semaforo__estado" id="sem-estado">Esperando código</p>
                    <p class="semaforo__nombre" id="sem-nombre">—</p>
                    <dl class="semaforo__datos">
                        <div><dt>Plan</dt><dd id="sem-plan">—</dd></div>
                        <div><dt>Vence</dt><dd id="sem-vence">—</dd></div>
                        <div><dt>Días restantes</dt><dd id="sem-dias">—</dd></div>
                    </dl>
                    <p class="semaforo__mensaje" id="sem-mensaje">Digita el código del socio para ver su membresía.</p>
                </div>
            </div>

            <button type="button" id="btn-registrar" class="btn btn-primary btn-lg w-100 mt-3" disabled>
                Registrar asistencia
            </button>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-body pb-2 d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Asistencias de hoy</h2>
                <span class="badge text-bg-secondary" id="total-hoy"><?= e(count($asistencias)) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr><th>Hora</th><th>Código</th><th>Socio</th></tr>
                    </thead>
                    <tbody id="lista-asistencias">
                    <?php foreach ($asistencias as $a): ?>
                        <tr>
                            <td><?= e($a['hora']) ?></td>
                            <td><?= e($a['codigo']) ?></td>
                            <td><?= e($a['nombre']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="fila-vacia" <?= $asistencias ? 'hidden' : '' ?>>
                        <td colspan="3" class="text-center text-muted py-3">Aún no hay asistencias hoy.</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>