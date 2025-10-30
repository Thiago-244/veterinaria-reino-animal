<?php
// Iniciar la sesión solo si no está iniciada
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Definir la ruta raíz del proyecto para usarla en toda la aplicación
define('APPROOT', dirname(dirname(__FILE__)));

// Cargar el autoloader de Composer
require_once APPROOT . '/vendor/autoload.php';

// Cargar el archivo de configuración
require_once APPROOT . '/config/config.php';

// Guard de acceso global: bloquear rutas privadas si no hay sesión
use App\Core\Auth;

$path = isset($_GET['url']) ? trim($_GET['url'], '/') : '';
$first = $path !== '' ? explode('/', $path)[0] : '';

$publicPrefixes = ['login', 'apilogin'];
$isPublic = ($first === '') || in_array(strtolower($first), $publicPrefixes, true);

if (!$isPublic && !Auth::check()) {
    header('Location: ' . APP_URL . '/login');
    exit;
}

// Iniciar el enrutador para que maneje la petición
$router = new App\Core\Router();