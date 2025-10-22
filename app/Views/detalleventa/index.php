<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="alert-container">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
</div>

<div class="actions">
    <a href="<?php echo APP_URL; ?>/detalleventa/estadisticas" class="btn-estadisticas">Ver Estadísticas</a>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Venta</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($detallesVenta)): ?>
            <tr>
                <td colspan="7" class="no-data">No hay detalles de venta registrados.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($detallesVenta as $detalle): ?>
                <tr>
                    <td><?php echo (int)$detalle['id']; ?></td>
                    <td>Venta #<?php echo (int)$detalle['id_venta']; ?></td>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre'] . ' (' . $detalle['producto_tipo'] . ')'); ?></td>
                    <td><?php echo (int)$detalle['cantidad']; ?></td>
                    <td>S/ <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td>S/ <?php echo number_format($detalle['subtotal'], 2); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/detalleventa/editar/<?php echo (int)$detalle['id']; ?>">Editar</a>
                        | <a href="<?php echo APP_URL; ?>/detalleventa/eliminar/<?php echo (int)$detalle['id']; ?>" onclick="return confirm('¿Eliminar detalle de venta?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
.alert-container {
    margin: 20px 0;
}

.alert {
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.actions {
    margin-bottom: 20px;
}

.btn-estadisticas {
    background-color: #17a2b8;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}

.btn-estadisticas:hover {
    background-color: #138496;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e9ecef;
}

.no-data {
    text-align: center;
    font-style: italic;
    color: #6c757d;
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
