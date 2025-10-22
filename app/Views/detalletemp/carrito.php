<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="carrito-info">
    <div class="info-row">
        <strong>Token Usuario:</strong> <?php echo htmlspecialchars($token_usuario); ?>
    </div>
    <div class="info-row">
        <strong>Total Items:</strong> <?php echo $estadisticas['total_items'] ?? 0; ?>
    </div>
    <div class="info-row">
        <strong>Total Carrito:</strong> <span class="total-carrito">S/ <?php echo number_format($estadisticas['total_carrito'] ?? 0, 2); ?></span>
    </div>
</div>

<div class="carrito-actions">
    <a href="<?php echo APP_URL; ?>/detalletemp/vaciar-carrito/<?php echo urlencode($token_usuario); ?>" 
       class="btn-vaciar" onclick="return confirm('¿Vaciar carrito?');">Vaciar Carrito</a>
    <a href="<?php echo APP_URL; ?>/detalletemp" class="btn-volver">Volver al Listado</a>
</div>

<?php if (!empty($detallesTemp)): ?>
    <h2>Productos en el Carrito</h2>
    
    <table>
        <thead>
            <tr>
                <th>Producto/Servicio</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detallesTemp as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                    <td>
                        <span class="badge-tipo badge-<?php echo strtolower($detalle['producto_tipo']); ?>">
                            <?php echo htmlspecialchars($detalle['producto_tipo']); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="<?php echo APP_URL; ?>/detalletemp/actualizar-cantidad/<?php echo (int)$detalle['id']; ?>" style="display: inline;">
                            <input type="number" name="cantidad" value="<?php echo htmlspecialchars($detalle['cantidad']); ?>" 
                                   min="0" max="999" style="width: 60px;">
                            <button type="submit" class="btn-actualizar">Actualizar</button>
                        </form>
                    </td>
                    <td>S/ <?php echo number_format($detalle['producto_precio'], 2); ?></td>
                    <td>
                        <span class="subtotal">S/ <?php echo number_format($detalle['cantidad'] * $detalle['producto_precio'], 2); ?></span>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/detalletemp/eliminar/<?php echo (int)$detalle['id']; ?>" 
                           onclick="return confirm('¿Eliminar del carrito?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total-label"><strong>Total:</strong></td>
                <td class="total-amount">
                    <strong>S/ <?php echo number_format($estadisticas['total_carrito'] ?? 0, 2); ?></strong>
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="procesar-venta">
        <h3>Procesar Venta</h3>
        <form method="POST" action="<?php echo APP_URL; ?>/detalletemp/procesar-venta/<?php echo urlencode($token_usuario); ?>">
            <div class="form-group">
                <label for="id_usuario">Usuario:</label>
                <select id="id_usuario" name="id_usuario" required>
                    <option value="">Seleccionar usuario...</option>
                    <!-- Aquí se cargarían los usuarios disponibles -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_cliente">Cliente:</label>
                <select id="id_cliente" name="id_cliente" required>
                    <option value="">Seleccionar cliente...</option>
                    <!-- Aquí se cargarían los clientes disponibles -->
                </select>
            </div>
            
            <button type="submit" class="btn-procesar">Procesar Venta</button>
        </form>
    </div>
<?php else: ?>
    <div class="carrito-vacio">
        <p>El carrito está vacío.</p>
    </div>
<?php endif; ?>

<style>
    .carrito-info {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .info-row {
        margin-bottom: 10px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-row strong {
        display: inline-block;
        width: 150px;
        color: #495057;
    }
    
    .total-carrito {
        font-weight: bold;
        color: #28a745;
        font-size: 18px;
    }
    
    .carrito-actions {
        margin-bottom: 30px;
    }
    
    .btn-vaciar, .btn-volver {
        display: inline-block;
        margin-right: 10px;
        padding: 10px 15px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .btn-vaciar {
        background-color: #dc3545;
    }
    
    .btn-volver {
        background-color: #6c757d;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
    }
    
    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .badge-tipo {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .badge-producto {
        background-color: #007bff;
        color: white;
    }
    
    .badge-servicio {
        background-color: #6f42c1;
        color: white;
    }
    
    .subtotal {
        font-weight: bold;
        color: #495057;
    }
    
    .btn-actualizar {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .btn-actualizar:hover {
        background-color: #0056b3;
    }
    
    tfoot {
        background-color: #e9ecef;
        font-weight: bold;
    }
    
    .total-label {
        text-align: right;
    }
    
    .total-amount {
        color: #28a745;
        font-size: 16px;
    }
    
    .procesar-venta {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 20px;
        margin-top: 30px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }
    
    select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        max-width: 300px;
    }
    
    .btn-procesar {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }
    
    .btn-procesar:hover {
        background-color: #218838;
    }
    
    .carrito-vacio {
        text-align: center;
        padding: 40px;
        color: #6c757d;
        font-size: 18px;
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
