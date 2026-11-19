<?php
declare(strict_types=1);

final class UsuarioController extends Controller
{
    /** Todo el módulo es exclusivo del administrador (RT-07). */
    public function __construct()
    {
        parent::__construct();
        $this->requerirRol('administrador');
    }

    public function listar(): void
    {
        $busqueda = $this->busqueda();

        $this->render('usuarios/listar', [
            'titulo'   => 'Usuarios',
            'usuarios' => Usuario::listar($busqueda),
            'busqueda' => $busqueda,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, ['usuario' => '', 'nombre' => '', 'rol' => 'recepcionista']);
    }

    public function guardar(): void
    {
        $this->protegerAccion();
        $v = new Validar();
        $datos = $this->leer($v, null);

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario(null, $datos, $v->errores());
            return;
        }

        Usuario::crear($datos['usuario'], $datos['nombre'], $datos['rol'], (string) $datos['hash']);
        flash('success', "Usuario {$datos['usuario']} creado.");
        redirect('usuario', 'listar');
    }

    public function editar(): void
    {
        $u = $this->registro($_GET['id'] ?? null, Usuario::buscar(...), 'Usuario');
        $this->formulario($u, [
            'usuario' => $u['usuario'],
            'nombre'  => $u['nombre'],
            'rol'     => $u['rol'],
        ]);
    }

    public function actualizar(): void
    {
        $this->protegerAccion();
        $u = $this->registro($_POST['id'] ?? null, Usuario::buscar(...), 'Usuario');
        $v = new Validar();
        $datos = $this->leer($v, $u);

        $bloqueo = $this->bloqueoAdministracion($u, $datos['rol']);
        if ($bloqueo !== null) {
            $v->agregar($bloqueo);
        }

        if (!$v->valido()) {
            http_response_code(422);
            $this->formulario($u, $datos, $v->errores());
            return;
        }

        Usuario::actualizar((int) $u['id'], $datos['nombre'], $datos['rol'], $datos['hash']);
        flash('success', 'Usuario actualizado.');
        redirect('usuario', 'listar');
    }

    /** Activa o desactiva. Nadie se desactiva a sí mismo ni deja el sistema sin administradores. */
    public function estado(): void
    {
        $this->protegerAccion();
        $u = $this->registro($_POST['id'] ?? null, Usuario::buscar(...), 'Usuario');
        $activar = !(bool) $u['activo'];

        if ((int) $u['id'] === Auth::id()) {
            flash('warning', 'No puedes desactivar tu propia cuenta.');
        } elseif (!$activar && $this->esUltimoAdmin($u)) {
            flash('warning', 'Debe quedar al menos un administrador activo.');
        } else {
            Usuario::cambiarActivo((int) $u['id'], $activar);
            flash('success', $activar ? 'Usuario activado.' : 'Usuario desactivado. Ya no podrá iniciar sesión.');
        }
        redirect('usuario', 'listar');
    }

    /** Eliminación solo si el usuario no tiene registros: si los tiene, se desactiva para conservar el historial. */
    public function eliminar(): void
    {
        $this->protegerAccion();
        $u = $this->registro($_POST['id'] ?? null, Usuario::buscar(...), 'Usuario');

        if ((int) $u['id'] === Auth::id()) {
            flash('warning', 'No puedes eliminar tu propia cuenta.');
        } elseif ($this->esUltimoAdmin($u)) {
            flash('warning', 'Debe quedar al menos un administrador activo.');
        } elseif (Usuario::registros((int) $u['id']) > 0) {
            flash('warning', 'El usuario tiene membresías o asistencias registradas. Desactívalo para conservar el historial.');
        } else {
            Usuario::eliminar((int) $u['id']);
            flash('success', 'Usuario eliminado.');
        }
        redirect('usuario', 'listar');
    }

    private function leer(Validar $v, ?array $actual): array
    {
        if ($actual === null) {
            $usuario = strtolower($v->texto($_POST['usuario'] ?? null, 'El usuario', 30));
            $v->patron($usuario, '/^[a-z0-9._]{3,30}$/', 'El usuario');

            if ($usuario !== '' && Usuario::existeUsuario($usuario)) {
                $v->agregar('Ya existe un usuario con ese nombre.');
            }
        } else {
            $usuario = $actual['usuario']; // el nombre de inicio de sesión no se cambia
        }

        $nombre = $v->texto($_POST['nombre'] ?? null, 'El nombre', 100);
        $rol = $v->opcion($_POST['rol'] ?? null, Usuario::ROLES, 'El rol');
        $hash = $this->hashPassword($v, $_POST['password'] ?? null, $actual === null);

        return ['usuario' => $usuario, 'nombre' => $nombre, 'rol' => $rol, 'hash' => $hash];
    }

    /** Contraseña obligatoria al crear, opcional al editar. Solo se guarda el hash (RT-06). */
    private function hashPassword(Validar $v, mixed $clave, bool $obligatoria): ?string
    {
        $clave = is_string($clave) ? $clave : '';

        if ($clave === '') {
            if ($obligatoria) {
                $v->agregar('La contraseña es obligatoria.');
            }
            return null;
        }

        $largo = strlen($clave);
        if ($largo < 10 || $largo > 72 || preg_match('/[A-Za-z]/', $clave) !== 1 || preg_match('/\d/', $clave) !== 1) {
            $v->agregar('La contraseña debe tener entre 10 y 72 caracteres, con al menos una letra y un número.');
            return null;
        }

        return password_hash($clave, PASSWORD_DEFAULT);
    }

    /** Quitar el rol de administrador: no a uno mismo y no si es el último administrador activo. */
    private function bloqueoAdministracion(array $u, string $nuevoRol): ?string
    {
        if ($u['rol'] !== 'administrador' || $nuevoRol === 'administrador' || !(bool) $u['activo']) {
            return null;
        }

        if ((int) $u['id'] === Auth::id()) {
            return 'No puedes quitarte a ti mismo el rol de administrador.';
        }

        if (Usuario::administradoresActivos() <= 1) {
            return 'Debe quedar al menos un administrador activo.';
        }

        return null;
    }

    private function esUltimoAdmin(array $u): bool
    {
        return $u['rol'] === 'administrador'
            && (bool) $u['activo']
            && Usuario::administradoresActivos() <= 1;
    }

    private function formulario(?array $usuario, array $valores, array $errores = []): void
    {
        $this->render('usuarios/form', [
            'titulo'  => $usuario ? 'Editar usuario' : 'Nuevo usuario',
            'usuario' => $usuario,
            'valores' => $valores,
            'errores' => $errores,
            'roles'   => Usuario::ROLES,
        ]);
    }
}