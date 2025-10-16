<?php
namespace App\Core;

class BaseController {
    
    public function model($model) {
        // Carga y crea una instancia del modelo solicitado
        require_once APPROOT . '/app/Models/' . $model . '.php';
        $fullModelName = 'App\\Models\\' . $model;
        return new $fullModelName();
    }

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