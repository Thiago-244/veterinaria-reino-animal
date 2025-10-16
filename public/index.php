<?php
// Iniciar la sesión
session_start();

// Definir la ruta raíz del proyecto para usarla en toda la aplicación
define('APPROOT', dirname(dirname(__FILE__)));

// Cargar el autoloader de Composer
require_once APPROOT . '/vendor/autoload.php';

// Cargar el archivo de configuración
require_once APPROOT . '/config/config.php';

// Iniciar el enrutador para que maneje la petición
$router = new App\Core\Router();