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
}