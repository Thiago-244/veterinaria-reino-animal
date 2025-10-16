<?php

namespace App\Controllers;

class HomeController {
    public function index() {
        echo "<h1>¡Bienvenido a la página principal!</h1>";
        echo "<p>El enrutador está funcionando y ha cargado el HomeController.</p>";
    }

    public function saludo($nombre = 'invitado') {
        echo "<h1>¡Hola, " . htmlspecialchars($nombre) . "!</h1>";
    }
}