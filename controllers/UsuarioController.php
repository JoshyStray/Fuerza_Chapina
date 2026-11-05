<?php
declare(strict_types=1);

final class UsuarioController extends Controller
{
    /** Todo este módulo es exclusivo del administrador (RT-07). */
    public function __construct()
    {
        parent::__construct();
        $this->requerirRol('administrador');
    }

    public function listar(): void
    {
        $this->render('usuarios/listar', [
            'titulo'   => 'Usuarios',
            'usuarios' => Usuario::todos(),
        ]);
    }
}