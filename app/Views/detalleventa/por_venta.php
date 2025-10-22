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

<div class="venta-info">
    <h2>Información de la Venta</h2>
    <div class="info-grid">
        <div class="info-item">
            <strong>ID Venta:</strong> <?php echo (int)$venta['id']; ?>
        </div>
        <div class="info-item">
            <strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?>
        </div>
        <div class="info-item">
            <strong>Usuario:</strong> <?php echo htmlspecialchars($venta['usuario_nombre']); ?>
        </div>
        <div class="info-item">
            <strong>Total:</strong> S/ <?php echo number_format($venta['total'], 2); ?>
        </div>
        <div class="info-item">
            <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['creado_en'])); ?>
        </div>
    </div>
</div>

<div class="actions">
    <a href="<?php echo APP_URL; ?>/detalleventa/crear/<?php echo (int)$venta['id']; ?>" class="btn-agregar">Agregar Detalle</a>
    <a href="<?php echo APP_URL; ?>/venta" class="btn-volver">Volver a Ventas</a>
</div>

<table>
    <thead>
        <tr>
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
                <td colspan="5" class="no-data">No hay detalles registrados para esta venta.</td>
            </tr>
        <?php else: ?>
            <?php 
            $totalCalculado = 0;
            foreach ($detallesVenta as $detalle): 
                $totalCalculado += $detalle['subtotal'];
            ?>
                <tr>
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
    <tfoot>
        <tr class="total-row">
            <td colspan="3"><strong>Total Calculado:</strong></td>
            <td><strong>S/ <?php echo number_format($totalCalculado, 2); ?></strong></td>
            <td></td>
        </tr>
    </tfoot>
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

.venta-info {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.venta-info h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    background-color: white;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.actions {
    margin-bottom: 20px;
}

.btn-agregar {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
    margin-right: 10px;
}

.btn-agregar:hover {
    background-color: #218838;
}

.btn-volver {
    background-color: #6c757d;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}

.btn-volver:hover {
    background-color: #5a6268;
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

.total-row {
    background-color: #e9ecef;
    font-weight: bold;
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
