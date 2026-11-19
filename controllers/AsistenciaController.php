<?php
declare(strict_types=1);

final class AsistenciaController extends Controller
{
    public function listar(): void
    {
        $hoy = date('Y-m-d');
        $desde = Validar::fechaOpcional($_GET['desde'] ?? null, $hoy);
        $hasta = Validar::fechaOpcional($_GET['hasta'] ?? null, $hoy);

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $busqueda = $this->busqueda();

        $this->render('asistencias/listar', [
            'titulo'      => 'Asistencias',
            'asistencias' => Asistencia::listar($desde, $hasta, $busqueda),
            'desde'       => $desde,
            'hasta'       => $hasta,
            'busqueda'    => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, ['socio_id' => '', 'fecha_hora' => date('Y-m-d\TH:i')]);
    }

    public function guardar(): void
    {
        $this->protegerAccion();
        $v = new Validar();
        $datos = $this->leer($v, null);

        if ($v->valido() && !Asistencia::insertar($datos['socio_id'], Auth::id(), $datos['fecha_hora'])) {
            $v->agregar('El socio ya tiene una asistencia registrada ese día (RN-04).');
        }

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario(null, $datos, $v->errores());
            return;
        }

        flash('success', 'Asistencia registrada.');
        redirect('asistencia', 'listar');
    }

    /** Solo administrador: corrige una asistencia registrada por error. */
    public function editar(): void
    {
        $this->requerirRol('administrador');
        $a = $this->registro($_GET['id'] ?? null, Asistencia::buscar(...), 'Asistencia');

        $this->formulario($a, [
            'socio_id'   => $a['socio_id'],
            'fecha_hora' => date('Y-m-d\TH:i', strtotime($a['fecha_hora'])),
        ]);
    }

    public function actualizar(): void
    {
        $this->protegerAccion('administrador');
        $a = $this->registro($_POST['id'] ?? null, Asistencia::buscar(...), 'Asistencia');
        $v = new Validar();
        $datos = $this->leer($v, (int) $a['id']);

        if ($v->valido() && !Asistencia::actualizar((int) $a['id'], $datos['socio_id'], $datos['fecha_hora'])) {
            $v->agregar('El socio ya tiene otra asistencia registrada ese día (RN-04).');
        }

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario($a, $datos, $v->errores());
            return;
        }

        flash('success', 'Asistencia actualizada.');
        redirect('asistencia', 'listar');
    }

    public function eliminar(): void
    {
        $this->protegerAccion('administrador');
        $a = $this->registro($_POST['id'] ?? null, Asistencia::buscar(...), 'Asistencia');

        Asistencia::eliminar((int) $a['id']);
        flash('success', 'Asistencia eliminada.');
        redirect('asistencia', 'listar');
    }

    /** Valida socio, fecha y las reglas RN-03 (membresía vigente ese día) y RN-04 (una por día). */
    private function leer(Validar $v, ?int $excluirId): array
    {
        $socioId = $v->entero($_POST['socio_id'] ?? null, 'El socio');
        $fechaHora = $v->fechaHora($_POST['fecha_hora'] ?? null, 'La fecha y hora');

        if ($socioId > 0 && $fechaHora !== '') {
            $fecha = substr($fechaHora, 0, 10);
            $socio = Socio::buscar($socioId);

            if ($socio === null || !$socio['activo']) {
                $v->agregar('Selecciona un socio activo.');
            } elseif ($fechaHora > date('Y-m-d H:i:s')) {
                $v->agregar('La asistencia no puede registrarse en el futuro.');
            } elseif (!Asistencia::socioVigenteEn($socioId, $fecha)) {
                $v->agregar('El socio no tenía una membresía vigente en esa fecha (RN-03).');
            } elseif (Asistencia::existeEn($socioId, $fecha, $excluirId)) {
                $v->agregar('El socio ya tiene una asistencia registrada ese día (RN-04).');
            }
        }

        return ['socio_id' => $socioId, 'fecha_hora' => $fechaHora];
    }

    private function formulario(?array $asistencia, array $valores, array $errores = []): void
    {
        $this->render('asistencias/form', [
            'titulo'     => $asistencia ? 'Editar asistencia' : 'Registrar asistencia',
            'asistencia' => $asistencia,
            'valores'    => $valores,
            'errores'    => $errores,
            'socios'     => Socio::activos(),
            'maximo'     => date('Y-m-d\TH:i'),
        ]);
    }
}