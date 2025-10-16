<?php
namespace App\Core;

class BaseController {
    /**
     * Carga una vista y le pasa datos.
     */
    public function view($view, $data = []) {
        // Construir la ruta al archivo de la vista
        $viewFile = APPROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            // Convierte las claves del array $data en variables (ej: $data['titulo'] se convierte en $titulo)
            extract($data);
            
            // Carga el archivo de la vista, que ahora tiene acceso a las variables
            require_once $viewFile;
        } else {
            // Si la vista no existe, muestra un error claro.
            die('Error: La vista no existe en: ' . $viewFile);
        }
    }
}