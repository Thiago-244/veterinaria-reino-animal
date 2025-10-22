<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/mascota/crear" class="btn-nuevo">Añadir Nueva Mascota</a>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Cliente</th>
            <th>Especie/Raza</th>
            <th>Sexo</th>
            <th>Edad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($mascotas as $mascota): ?>
            <tr>
                <td><?php echo htmlspecialchars($mascota['codigo']); ?></td>
                <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                <td><?php echo htmlspecialchars($mascota['cliente_nombre'] . ' ' . $mascota['cliente_apellido']); ?></td>
                <td><?php echo htmlspecialchars($mascota['especie_nombre'] . ' - ' . $mascota['raza_nombre']); ?></td>
                <td><?php echo htmlspecialchars($mascota['sexo']); ?></td>
                <td>
                    <?php 
                    if ($mascota['fecha_nacimiento']) {
                        $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                        $hoy = new DateTime();
                        $edad = $hoy->diff($nacimiento);
                        echo $edad->y . ' años';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/mascota/editar/<?php echo (int)$mascota['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/mascota/eliminar/<?php echo (int)$mascota['id']; ?>" onclick="return confirm('¿Eliminar mascota?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .btn-nuevo {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 15px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    a {
        color: #007bff;
        text-decoration: none;
    }
    
    a:hover {
        text-decoration: underline;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
