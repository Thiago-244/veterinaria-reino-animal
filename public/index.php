<?php
session_start();

// =================================================================
// DEFINIR LA RUTA RAÍZ DEL PROYECTO
// =================================================================
// Creamos una constante global 'APPROOT' que contiene la ruta absoluta
// a la carpeta raíz del proyecto (ej: C:/xampp/htdocs/Veterinaria_CS_G4).
// Esto elimina todos los problemas de rutas relativas (../)
define('APPROOT', dirname(dirname(__FILE__)));


// Cargar dependencias y configuración usando la ruta absoluta
require_once APPROOT . '/vendor/autoload.php';
require_once APPROOT . '/config/config.php';


// Inicializar el Enrutador
$router = new App\Core\Router();