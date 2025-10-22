<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="venta-info">
    <div class="info-row">
        <strong>ID de Venta:</strong> <?php echo htmlspecialchars($venta['id']); ?>
    </div>
    <div class="info-row">
        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s', strtotime($venta['creado_en'])); ?>
    </div>
    <div class="info-row">
        <strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?>
        <br><small>DNI: <?php echo htmlspecialchars($venta['cliente_dni']); ?></small>
    </div>
    <div class="info-row">
        <strong>Usuario:</strong> <?php echo htmlspecialchars($venta['usuario_nombre']); ?>
    </div>
    <div class="info-row">
        <strong>Total:</strong> <span class="total-venta">S/ <?php echo number_format($venta['total'], 2); ?></span>
    </div>
</div>

<h2>Detalles de la Venta</h2>

<table>
    <thead>
        <tr>
            <th>Producto/Servicio</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detalles as $detalle): ?>
            <tr>
                <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                <td>
                    <span class="badge-tipo badge-<?php echo strtolower($detalle['producto_tipo']); ?>">
                        <?php echo htmlspecialchars($detalle['producto_tipo']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                <td>S/ <?php echo number_format($detalle['precio'], 2); ?></td>
                <td>
                    <span class="subtotal">S/ <?php echo number_format($detalle['cantidad'] * $detalle['precio'], 2); ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="total-label"><strong>Total:</strong></td>
            <td class="total-amount">
                <strong>S/ <?php echo number_format($venta['total'], 2); ?></strong>
            </td>
        </tr>
    </tfoot>
</table>

<div class="actions">
    <a href="<?php echo APP_URL; ?>/venta" class="btn-volver">Volver al Listado</a>
</div>

<style>
    .venta-info {
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
        width: 120px;
        color: #495057;
    }
    
    .total-venta {
        font-weight: bold;
        color: #28a745;
        font-size: 18px;
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
    
    .actions {
        margin-top: 20px;
    }
    
    .btn-volver {
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 16px;
        display: inline-block;
    }
    
    .btn-volver:hover {
        background-color: #5a6268;
        text-decoration: none;
    }
    
    small {
        color: #6c757d;
        font-size: 12px;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
