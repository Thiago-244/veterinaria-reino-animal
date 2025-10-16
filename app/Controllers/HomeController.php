<?php
namespace App\Controllers;

use App\Core\BaseController;

class HomeController extends BaseController {

    public function index() {
        $data = [
            'titulo' => '¡Bienvenido a Veterinaria Reino Animal!',
            'descripcion' => 'Sistema de gestión de calidad funcionando.'
        ];
        $this->view('home/index', $data);
    }

    public function saludo($nombre = 'invitado') {
        echo "<h1>¡Hola, " . htmlspecialchars($nombre) . "!</h1>";
    }
}