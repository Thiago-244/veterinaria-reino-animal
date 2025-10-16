<?php
namespace App\Core;

class BaseController {
    /**
     * Carga un modelo para que esté disponible en el controlador.
     */
    public function model($model) {
        require_once APPROOT . '/app/Models/' . $model . '.php';
        $fullModelName = 'App\\Models\\' . $model;
        return new $fullModelName();
    }

    /**
     * Carga una vista y le pasa datos.
     */
    public function view($view, $data = []) {
        $viewFile = APPROOT . '/app/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            die('Error: La vista no existe en: ' . $viewFile);
        }
    }
}