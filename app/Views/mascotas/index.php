<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/mascota/crear" class="btn btn--success mb-3">Añadir Nueva Mascota</a>

<table class="table">
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
                <td><span class="badge badge--info"><?php echo htmlspecialchars($mascota['especie_nombre'] . ' - ' . $mascota['raza_nombre']); ?></span></td>
                <td><span class="badge badge--dark"><?php echo htmlspecialchars($mascota['sexo']); ?></span></td>
                <td>
                    <?php 
                    if ($mascota['fecha_nacimiento']) {
                        $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                        $hoy = new DateTime();
                        $edad = $hoy->diff($nacimiento);
                        echo '<span class="badge badge--success">' . $edad->y . ' años</span>';
                    } else {
                        echo '<span class="badge badge--warn">N/A</span>';
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/mascota/editar/<?php echo (int)$mascota['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/mascota/eliminar/<?php echo (int)$mascota['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar mascota?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
