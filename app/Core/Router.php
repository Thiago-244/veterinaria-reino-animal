<?php
namespace App\Core;

class Router {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Determinar el controlador y su ubicación
        $controllerName = !empty($url[0]) ? ucwords($url[0]) . 'Controller' : 'HomeController';
        
        // Manejar prefijos como "api"
        $moduleName = '';
        $actualControllerName = $controllerName;
        
        if (!empty($url[0])) {
            $firstSegment = $url[0];
            if (strpos($firstSegment, 'api') === 0) {
                // Para URLs como "apicliente", extraer "cliente"
                $moduleName = str_replace('api', '', $firstSegment);
                $actualControllerName = 'Api' . ucwords($moduleName) . 'Controller';
            } else {
                $moduleName = $firstSegment;
            }
        }
        
        $controllerFile = '';
        $controllerNamespace = '';
        
        if ($moduleName && file_exists(APPROOT . '/app/Controllers/' . ucwords($moduleName) . '/' . $actualControllerName . '.php')) {
            // Controlador en subcarpeta de módulo
            $controllerFile = APPROOT . '/app/Controllers/' . ucwords($moduleName) . '/' . $actualControllerName . '.php';
            $controllerNamespace = 'App\\Controllers\\' . ucwords($moduleName) . '\\' . $actualControllerName;
        } elseif (file_exists(APPROOT . '/app/Controllers/' . $actualControllerName . '.php')) {
            // Controlador en carpeta raíz
            $controllerFile = APPROOT . '/app/Controllers/' . $actualControllerName . '.php';
            $controllerNamespace = 'App\\Controllers\\' . $actualControllerName;
        }

        if ($controllerFile) {
            $this->controller = $actualControllerName;
            unset($url[0]);
        }

        if ($controllerFile) {
            require_once $controllerFile;
            $this->controller = new $controllerNamespace;
        }

        if (isset($url[1])) {
            // Convertir guiones a camelCase para nombres de métodos
            $methodName = str_replace('-', '', ucwords($url[1], '-'));
            $methodName = lcfirst($methodName);
            
            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } elseif (method_exists($this->controller, $url[1])) {
                // Fallback: intentar con el nombre original
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}