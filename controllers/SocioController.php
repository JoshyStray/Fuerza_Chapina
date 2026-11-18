<?php
declare(strict_types=1);

final class SocioController extends Controller
{
    public function listar(): void
    {
        $busqueda = $this->busqueda();

        $this->render('socios/listar', [
            'titulo'   => 'Socios',
            'socios'   => Socio::listar($busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, ['nombre' => '', 'telefono' => '']);
    }

    public function guardar(): void
    {
        $this->protegerAccion();
        $v = new Validar();
        $datos = $this->leer($v);

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario(null, $datos, $v->errores());
            return;
        }

        $codigo = Socio::crear($datos['nombre'], $datos['telefono'] !== '' ? $datos['telefono'] : null);
        flash('success', "Socio registrado con el código {$codigo}.");
        redirect('socio', 'listar');
    }

    public function editar(): void
    {
        $socio = $this->registro($_GET['id'] ?? null, Socio::buscar(...), 'Socio');
        $this->formulario($socio, [
            'nombre'   => $socio['nombre'],
            'telefono' => $socio['telefono'] ?? '',
        ]);
    }

    public function actualizar(): void
    {
        $this->protegerAccion();
        $socio = $this->registro($_POST['id'] ?? null, Socio::buscar(...), 'Socio');
        $v = new Validar();
        $datos = $this->leer($v);

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario($socio, $datos, $v->errores());
            return;
        }

        Socio::actualizar((int) $socio['id'], $datos['nombre'], $datos['telefono'] !== '' ? $datos['telefono'] : null);
        flash('success', 'Datos del socio actualizados.');
        redirect('socio', 'listar');
    }

    /** Solo administrador: activa o desactiva (borrado lógico). */
    public function estado(): void
    {
        $this->protegerAccion('administrador');
        $socio = $this->registro($_POST['id'] ?? null, Socio::buscar(...), 'Socio');
        $activar = !(bool) $socio['activo'];

        Socio::cambiarActivo((int) $socio['id'], $activar);
        flash('success', $activar
            ? 'Socio reactivado.'
            : 'Socio desactivado: ya no podrá registrar asistencias.');
        redirect('socio', 'listar');
    }

    /** Solo administrador. Si el socio tiene membresías o asistencias, no se elimina. */
    public function eliminar(): void
    {
        $this->protegerAccion('administrador');
        $socio = $this->registro($_POST['id'] ?? null, Socio::buscar(...), 'Socio');

        if (Socio::eliminar((int) $socio['id'])) {
            flash('success', 'Socio eliminado.');
        } else {
            flash('warning', 'No se puede eliminar: el socio tiene membresías o asistencias. Desactívalo en su lugar.');
        }
        redirect('socio', 'listar');
    }

    private function leer(Validar $v): array
    {
        $nombre = $v->texto($_POST['nombre'] ?? null, 'El nombre', 100);
        $telefono = $v->texto($_POST['telefono'] ?? null, 'El teléfono', 15, false);
        $v->patron($telefono, '/^[0-9+\- ]{8,15}$/', 'El teléfono');

        return ['nombre' => $nombre, 'telefono' => $telefono];
    }

    private function formulario(?array $socio, array $valores, array $errores = []): void
    {
        $this->render('socios/form', [
            'titulo'  => $socio ? 'Editar socio' : 'Nuevo socio',
            'socio'   => $socio,
            'valores' => $valores,
            'errores' => $errores,
        ]);
    }
}