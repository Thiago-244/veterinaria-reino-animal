<?php require APPROOT . '/Views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<table>
    <thead>
        <tr>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Teléfono</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?php echo htmlspecialchars($cliente['dni']); ?></td>
                <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                <td><?php echo htmlspecialchars($cliente['apellido']); ?></td>
                <td><?php echo htmlspecialchars($cliente['telefono']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/Views/layouts/footer.php'; ?>