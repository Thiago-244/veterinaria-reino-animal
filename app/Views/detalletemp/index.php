<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/detalletemp/estadisticas" class="btn-estadisticas">Estadísticas</a>
<a href="<?php echo APP_URL; ?>/detalletemp/limpiar-antiguos" class="btn-limpiar">Limpiar Antiguos</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Producto/Servicio</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Precio Unit.</th>
            <th>Subtotal</th>
            <th>Token Usuario</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detallesTemp as $detalle): ?>
            <tr>
                <td><?php echo htmlspecialchars($detalle['id']); ?></td>
                <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                <td>
                    <span class="badge-tipo badge-<?php echo strtolower($detalle['producto_tipo']); ?>">
                        <?php echo htmlspecialchars($detalle['producto_tipo']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                <td>S/ <?php echo number_format($detalle['producto_precio'], 2); ?></td>
                <td>
                    <span class="subtotal">S/ <?php echo number_format($detalle['cantidad'] * $detalle['producto_precio'], 2); ?></span>
                </td>
                <td>
                    <small class="token"><?php echo htmlspecialchars(substr($detalle['token_usuario'], 0, 20)) . '...'; ?></small>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($detalle['creado_en'])); ?></td>
                <td>
                    <a href="<?php echo APP_URL; ?>/detalletemp/eliminar/<?php echo (int)$detalle['id']; ?>" 
                       onclick="return confirm('¿Eliminar del carrito?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .btn-estadisticas, .btn-limpiar {
        display: inline-block;
        margin-bottom: 20px;
        margin-right: 10px;
        padding: 10px 15px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .btn-estadisticas {
        background-color: #6f42c1;
    }
    
    .btn-limpiar {
        background-color: #dc3545;
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
    
    .token {
        color: #6c757d;
        font-size: 12px;
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
