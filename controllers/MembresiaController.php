<?php
declare(strict_types=1);

final class MembresiaController extends Controller
{
    public function listar(): void
    {
        $busqueda = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 50);

        $this->render('membresias/listar', [
            'titulo'     => 'Membresías',
            'membresias' => Membresia::listar($busqueda),
            'busqueda'   => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->mostrarFormulario(null, [
            'socio_id'     => '',
            'plan_id'      => '',
            'fecha_inicio' => date('Y-m-d'),
            'monto_pagado' => '',
        ]);
    }

    public function guardar(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();

        $r = $this->validar($_POST);
        if ($r['errores']) {
            http_response_code(422);
            $this->mostrarFormulario(null, $r['valores'], $r['errores']);
            return;
        }

        $v = $r['valores'];
        $vigencia = Membresia::calcularVigencia((int) $v['socio_id'], (int) $r['plan']['duracion_dias'], $v['fecha_inicio']);

        Membresia::crear([
            'socio_id'     => (int) $v['socio_id'],
            'plan_id'      => (int) $v['plan_id'],
            'usuario_id'   => Auth::id(),
            'fecha_inicio' => $vigencia['inicio'],
            'fecha_fin'    => $vigencia['fin'],
            'monto_pagado' => $v['monto_pagado'],
        ]);

        flash('success', sprintf('Membresía registrada: del %s al %s.', fecha($vigencia['inicio']), fecha($vigencia['fin'])));
        if ($vigencia['encadenada']) {
            flash('info', 'El socio aún tenía una membresía vigente, por eso la nueva inicia el día siguiente a su vencimiento.');
        }
        redirect('membresia', 'listar');
    }

    public function editar(): void
    {
        $this->requerirRol('administrador');

        $membresia = $this->obtenerMembresia($_GET['id'] ?? null);

        $this->mostrarFormulario($membresia, [
            'socio_id'     => $membresia['socio_id'],
            'plan_id'      => $membresia['plan_id'],
            'fecha_inicio' => $membresia['fecha_inicio'],
            'monto_pagado' => $membresia['monto_pagado'],
        ]);
    }

    public function actualizar(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();
        $this->requerirRol('administrador');

        $membresia = $this->obtenerMembresia($_POST['id'] ?? null);

        $r = $this->validar($_POST);
        if ($r['errores']) {
            http_response_code(422);
            $this->mostrarFormulario($membresia, $r['valores'], $r['errores']);
            return;
        }

        $v = $r['valores'];
        Membresia::actualizar((int) $membresia['id'], [
            'socio_id'     => (int) $v['socio_id'],
            'plan_id'      => (int) $v['plan_id'],
            'fecha_inicio' => $v['fecha_inicio'],
            'fecha_fin'    => Membresia::calcularFin($v['fecha_inicio'], (int) $r['plan']['duracion_dias']),
            'monto_pagado' => $v['monto_pagado'],
        ]);

        flash('success', 'Membresía actualizada correctamente.');
        redirect('membresia', 'listar');
    }

    public function eliminar(): void
    {
        $this->requerirPost();
        $this->verificarCsrf();
        $this->requerirRol('administrador');

        $membresia = $this->obtenerMembresia($_POST['id'] ?? null);
        Membresia::eliminar((int) $membresia['id']);

        flash('success', 'Membresía eliminada.');
        redirect('membresia', 'listar');
    }

    // ------------------------------------------------------------------

    private function obtenerMembresia(mixed $id): array
    {
        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $membresia = $id ? Membresia::buscar($id) : null;

        if ($membresia === null) {
            mostrarError(404, 'Membresía no encontrada', 'La membresía solicitada no existe.');
        }

        return $membresia;
    }

    private function mostrarFormulario(?array $membresia, array $valores, array $errores = []): void
    {
        $this->render('membresias/form', [
            'titulo'    => $membresia ? 'Editar membresía' : 'Nueva membresía',
            'membresia' => $membresia,
            'valores'   => $valores,
            'errores'   => $errores,
            'socios'    => Socio::activos(),
            'planes'    => Plan::activos(),
        ]);
    }

    /** Validación del lado del servidor: tipos, rangos, obligatorios y existencia. */
    private function validar(array $entrada): array
    {
        $errores = [];

        $socioId = filter_var($entrada['socio_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $planId  = filter_var($entrada['plan_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $inicio  = trim((string) ($entrada['fecha_inicio'] ?? ''));
        $monto   = trim((string) ($entrada['monto_pagado'] ?? ''));

        $socio = $socioId ? Socio::buscar($socioId) : null;
        if ($socio === null || !$socio['activo']) {
            $errores[] = 'Selecciona un socio válido y activo.';
        }

        $plan = $planId ? Plan::buscar($planId) : null;
        if ($plan === null || !$plan['activo']) {
            $errores[] = 'Selecciona un plan válido y activo.';
        }

        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $inicio);
        if ($fecha === false || $fecha->format('Y-m-d') !== $inicio) {
            $errores[] = 'La fecha de inicio no es válida.';
        }

        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $monto)) {
            $errores[] = 'El monto pagado debe ser un número positivo con máximo 2 decimales.';
        }

        return [
            'valores' => [
                'socio_id'     => $socioId ?: '',
                'plan_id'      => $planId ?: '',
                'fecha_inicio' => $inicio,
                'monto_pagado' => $monto,
            ],
            'errores' => $errores,
            'plan'    => $plan,
        ];
    }
}