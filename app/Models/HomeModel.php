<?php
namespace App\Models;

class HomeModel {

    public function getHomePageData() {
        // En el futuro, esta función hará una consulta a la base de datos.
        // Por ahora, simplemente devuelve un array con la información.
        return [
            'titulo' => 'Datos desde el Modelo',
            'descripcion' => '¡El ciclo MVC se ha completado con éxito!'
        ];
    }
}