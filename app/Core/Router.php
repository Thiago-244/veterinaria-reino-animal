<?php

namespace App\Core;

class Router {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // 1. Verificar si el controlador existe
        $controllerName = ucfirst($url[0] ?? $this->controller);
        $controllerFile = APPROOT . '/app/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            $this->controller = $controllerName;
            unset($url[0]);
        }

        // Incluir y crear la instancia del controlador
        require_once APPROOT . '/app/Controllers/' . $this->controller . '.php';
        $fullControllerName = 'App\\Controllers\\' . $this->controller;
        $this->controller = new $fullControllerName;

        // 2. Verificar si el método existe en el controlador
        if (isset($url[1])) {
            $methodName = $url[1];
            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            }
        }

        // 3. Obtener los parámetros de la URL
        $this->params = $url ? array_values($url) : [];

        // 4. Llamar al método con los parámetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parsea la URL para obtener controlador, método y parámetros.
     */
    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}