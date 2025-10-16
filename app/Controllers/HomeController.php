<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\HomeModel; // <-- 1. Importamos la clase del modelo

class HomeController extends BaseController {

    private $homeModel;

    public function __construct() {
        // 2. Creamos una instancia del modelo en el constructor
        // Ahora, cualquier método en este controlador puede usar $this->homeModel
        $this->homeModel = $this->model('HomeModel');
    }

    public function index() {
        // 3. Pedimos los datos al modelo
        $data = $this->homeModel->getHomePageData();

        // 4. Pasamos los datos obtenidos del modelo a la vista
        $this->view('home/index', $data);
    }

    public function saludo($nombre = 'invitado') {
        echo "<h1>¡Hola, " . htmlspecialchars($nombre) . "!</h1>";
    }
}