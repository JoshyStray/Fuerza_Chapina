<?php
declare(strict_types=1);

final class RecepcionController extends Controller
{
    public function index(): void
    {
        $this->render('recepcion/index', [
            'titulo'      => 'Recepción',
            'asistencias' => Asistencia::hoy(),
            'scripts'     => ['js/recepcion.js'],
        ]);
    }

    /** AJAX (GET): responde en JSON el estado del socio cuyo código se digitó. */
    public function consultar(): void
    {
        $codigo = $this->leerCodigo($_GET);
        $socio  = $codigo === '' ? null : Socio::consultaRecepcion($codigo);

        if ($socio === null) {
            responderJson([
                'ok'         => true,
                'encontrado' => false,
                'codigo'     => $codigo,
                'mensaje'    => 'No existe ningún socio con ese código.',
            ]);
        }

        $evaluacion = $this->evaluar($socio);

        responderJson([
            'ok'              => true,
            'encontrado'      => true,
            'socio'           => [
                'codigo'         => $socio['codigo'],
                'nombre'         => $socio['nombre'],
                'plan'           => $socio['plan'],
                'fecha_fin'      => $socio['fecha_fin'] ? fecha($socio['fecha_fin']) : null,
                'dias_restantes' => $socio['dias_restantes'],
                'estado'         => $socio['estado'],
            ],
            'puede_registrar' => $evaluacion['puede'],
            'mensaje'         => $evaluacion['mensaje'],
        ]);
    }

    /** AJAX (POST): registra la asistencia aplicando RN-03 y RN-04 en el servidor. */
    public function registrar(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();

        $codigo = $this->leerCodigo($_POST);
        $socio  = $codigo === '' ? null : Socio::consultaRecepcion($codigo);

        if ($socio === null) {
            responderJson(['ok' => false, 'mensaje' => 'No existe ningún socio con ese código.'], 404);
        }

        $evaluacion = $this->evaluar($socio);
        if (!$evaluacion['puede']) {
            responderJson(['ok' => false, 'mensaje' => $evaluacion['mensaje']], $evaluacion['http']);
        }

        if (!Asistencia::registrar($socio['id'], Auth::id())) {
            responderJson(['ok' => false, 'mensaje' => 'Este socio ya registró su asistencia hoy.'], 409);
        }

        responderJson([
            'ok'         => true,
            'mensaje'    => 'Asistencia registrada.',
            'asistencia' => [
                'hora'           => date('H:i'),
                'codigo'         => $socio['codigo'],
                'nombre'         => $socio['nombre'],
                'registrado_por' => Auth::usuario()['nombre'],
            ],
        ], 201);
    }

    // ------------------------------------------------------------------

    private function leerCodigo(array $fuente): string
    {
        $crudo = $fuente['codigo'] ?? '';
        if (!is_string($crudo)) {
            return '';
        }

        $codigo = Socio::normalizarCodigo($crudo);

        return strlen($codigo) <= 10 ? $codigo : '';
    }

    /** RN-03 (membresía vigente) y RN-04 (una asistencia por día). */
    private function evaluar(array $socio): array
    {
        $mensajes = [
            'inactivo'      => 'El socio está inactivo.',
            'sin_membresia' => 'El socio no tiene ninguna membresía registrada.',
            'vencida'       => 'La membresía venció el ' . fecha($socio['fecha_fin']) . '.',
            'programada'    => 'La membresía inicia el ' . fecha($socio['fecha_inicio']) . '.',
        ];

        if ($socio['estado'] !== 'vigente') {
            return ['puede' => false, 'mensaje' => $mensajes[$socio['estado']], 'http' => 422];
        }

        if (Asistencia::existeHoy($socio['id'])) {
            return ['puede' => false, 'mensaje' => 'Este socio ya registró su asistencia hoy.', 'http' => 409];
        }

        return ['puede' => true, 'mensaje' => 'Puede ingresar.', 'http' => 200];
    }
}