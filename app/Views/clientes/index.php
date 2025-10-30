<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/cliente/crear" class="btn btn--success mb-3">Añadir Nuevo Cliente</a>

<table class="table">
    <thead>
        <tr>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?php echo htmlspecialchars($cliente['dni']); ?></td>
                <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                <td><?php echo htmlspecialchars($cliente['apellido']); ?></td>
                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                <td>
                    <a href="<?php echo APP_URL; ?>/cliente/editar/<?php echo (int)$cliente['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/cliente/eliminar/<?php echo (int)$cliente['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar cliente?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>