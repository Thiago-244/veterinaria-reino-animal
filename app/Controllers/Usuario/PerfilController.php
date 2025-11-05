<?php
namespace App\Controllers\Usuario;

use App\Core\BaseController;
use App\Core\Auth;

class PerfilController extends BaseController {

    public function index() {
        Auth::middleware();
        $usuario = Auth::user();
        $session = Auth::sessionInfo();
        $data = [
            'titulo' => 'Mi Perfil',
            'usuario' => $usuario,
            'session' => $session,
        ];
        $this->view('usuarios/perfil', $data);
    }
}
