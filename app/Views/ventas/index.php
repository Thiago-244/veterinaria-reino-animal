<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/venta/crear" class="btn-nuevo">Nueva Venta</a>
<a href="<?php echo APP_URL; ?>/venta/ventas-del-dia" class="btn-dia">Ventas del Día</a>
<a href="<?php echo APP_URL; ?>/venta/reportes" class="btn-reportes">Reportes</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Usuario</th>
            <th>Total</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ventas as $venta): ?>
            <tr>
                <td><?php echo htmlspecialchars($venta['id']); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($venta['creado_en'])); ?></td>
                <td>
                    <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?>
                    <br><small>(<?php echo htmlspecialchars($venta['cliente_dni']); ?>)</small>
                </td>
                <td><?php echo htmlspecialchars($venta['usuario_nombre']); ?></td>
                <td>
                    <span class="total-venta">S/ <?php echo number_format($venta['total'], 2); ?></span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/venta/ver/<?php echo (int)$venta['id']; ?>">Ver</a>
                    |
                    <a href="<?php echo APP_URL; ?>/venta/eliminar/<?php echo (int)$venta['id']; ?>" 
                       onclick="return confirm('¿Eliminar venta?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .btn-nuevo, .btn-dia, .btn-reportes {
        display: inline-block;
        margin-bottom: 20px;
        margin-right: 10px;
        padding: 10px 15px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .btn-nuevo {
        background-color: #28a745;
    }
    
    .btn-dia {
        background-color: #007bff;
    }
    
    .btn-reportes {
        background-color: #6f42c1;
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
    
    .total-venta {
        font-weight: bold;
        color: #28a745;
        font-size: 16px;
    }
    
    small {
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
