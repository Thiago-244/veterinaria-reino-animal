<?php
namespace App\Core;

class Router {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        $controllerName = !empty($url[0]) ? ucwords($url[0]) . 'Controller' : 'HomeController';
        $controllerFile = APPROOT . '/app/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            $this->controller = $controllerName;
            unset($url[0]);
        }

        require_once APPROOT . '/app/Controllers/' . $this->controller . '.php';
        $fullControllerName = 'App\\Controllers\\' . $this->controller;
        $this->controller = new $fullControllerName;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
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