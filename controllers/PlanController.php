<?php
declare(strict_types=1);

final class PlanController extends Controller
{
    /** Consulta: ambos roles. Escritura: solo administrador (RT-07, propuesta §3). */
    public function listar(): void
    {
        $busqueda = $this->busqueda();

        $this->render('planes/listar', [
            'titulo'   => 'Planes',
            'planes'   => Plan::listar($busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->requerirRol('administrador');
        $this->formulario(null, ['nombre' => '', 'duracion_dias' => '', 'precio' => '']);
    }

    public function guardar(): void
    {
        $this->protegerAccion('administrador');
        $v = new Validar();
        $datos = $this->leer($v, null);

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario(null, $datos, $v->errores());
            return;
        }

        Plan::crear($datos['nombre'], $datos['duracion_dias'], $datos['precio']);
        flash('success', 'Plan creado.');
        redirect('plan', 'listar');
    }

    public function editar(): void
    {
        $this->requerirRol('administrador');
        $plan = $this->registro($_GET['id'] ?? null, [Plan::class, 'buscar'], 'Plan');
        $this->formulario($plan, [
            'nombre'        => $plan['nombre'],
            'duracion_dias' => $plan['duracion_dias'],
            'precio'        => $plan['precio'],
        ]);
    }

    public function actualizar(): void
    {
        $this->protegerAccion('administrador');
        $plan = $this->registro($_POST['id'] ?? null, [Plan::class, 'buscar'], 'Plan');
        $v = new Validar();
        $datos = $this->leer($v, (int) $plan['id']);

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario($plan, $datos, $v->errores());
            return;
        }

        Plan::actualizar((int) $plan['id'], $datos['nombre'], $datos['duracion_dias'], $datos['precio']);
        flash('success', 'Plan actualizado.');
        redirect('plan', 'listar');
    }

    public function estado(): void
    {
        $this->protegerAccion('administrador');
        $plan = $this->registro($_POST['id'] ?? null, [Plan::class, 'buscar'], 'Plan');
        $activar = !(bool) $plan['activo'];

        Plan::cambiarActivo((int) $plan['id'], $activar);
        flash('success', $activar ? 'Plan activado.' : 'Plan desactivado: ya no aparecerá al registrar membresías.');
        redirect('plan', 'listar');
    }

    public function eliminar(): void
    {
        $this->protegerAccion('administrador');
        $plan = $this->registro($_POST['id'] ?? null, [Plan::class, 'buscar'], 'Plan');

        if (Plan::eliminar((int) $plan['id'])) {
            flash('success', 'Plan eliminado.');
        } else {
            flash('warning', 'No se puede eliminar: el plan tiene membresías registradas. Desactívalo en su lugar.');
        }
        redirect('plan', 'listar');
    }

    private function leer(Validar $v, ?int $excluirId): array
    {
        $nombre = $v->texto($_POST['nombre'] ?? null, 'El nombre del plan', 50);
        $dias = $v->entero($_POST['duracion_dias'] ?? null, 'La duración en días', 1, 3650);
        $precio = $v->dinero($_POST['precio'] ?? null, 'El precio', true);

        if ($nombre !== '' && Plan::existeNombre($nombre, $excluirId)) {
            $v->agregar('Ya existe un plan con ese nombre.');
        }

        return ['nombre' => $nombre, 'duracion_dias' => $dias, 'precio' => $precio];
    }

    private function formulario(?array $plan, array $valores, array $errores = []): void
    {
        $this->render('planes/form', [
            'titulo'  => $plan ? 'Editar plan' : 'Nuevo plan',
            'plan'    => $plan,
            'valores' => $valores,
            'errores' => $errores,
        ]);
    }
}