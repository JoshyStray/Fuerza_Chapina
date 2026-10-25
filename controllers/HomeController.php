<?php
declare(strict_types=1);

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'titulo'  => 'Inicio',
            'resumen' => Membresia::resumen(),
        ]);
    }
}