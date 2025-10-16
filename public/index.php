<?php
/**
 * =================================================================
 * VETERINARIA REINO ANIMAL - FRONT CONTROLLER
 * =================================================================
 * Este es el único punto de entrada a la aplicación. Su trabajo es
 * inicializar el sistema y lanzar el enrutador.
 */

// Iniciar la sesión para poder usar variables $_SESSION
session_start();

// 1. Cargar el Autoloader de Composer
// Esta línea es la magia que nos permite usar nuestras clases (Controladores, Modelos)
// sin tener que hacer 'require' de cada archivo.
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Cargar las Variables de Entorno y Configuración
// Carga las constantes con las credenciales de la BD y la URL de la app.
require_once __DIR__ . '/../config/config.php';

// 3. (Próximamente) Inicializar el Enrutador
// Aquí es donde la aplicación cobrará vida.
// El enrutador leerá la URL y decidirá qué controlador ejecutar.

// Por ahora, vamos a poner una prueba final para asegurarnos de que todo carga.
echo "<h1>¡El Front Controller funciona!</h1>";
echo "<p>El autoloader y la configuración se han cargado correctamente.</p>";
echo "<p>La URL de la aplicación es: " . APP_URL . "</p>";

// El siguiente paso será reemplazar estas líneas de prueba con la llamada al Router.
// Ejemplo:
// $router = new App\Core\Router();
// $router->run();