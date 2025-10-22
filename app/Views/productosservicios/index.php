<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/productoservicio/crear" class="btn-nuevo">Nuevo Producto/Servicio</a>
<a href="<?php echo APP_URL; ?>/productoservicio/productos" class="btn-productos">Solo Productos</a>
<a href="<?php echo APP_URL; ?>/productoservicio/servicios" class="btn-servicios">Solo Servicios</a>
<a href="<?php echo APP_URL; ?>/productoservicio/stock-bajo" class="btn-stock-bajo">Stock Bajo</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productosServicios as $item): ?>
            <tr class="<?php echo strtolower($item['tipo']); ?>">
                <td><?php echo htmlspecialchars($item['id']); ?></td>
                <td>
                    <span class="badge-tipo badge-<?php echo strtolower($item['tipo']); ?>">
                        <?php echo htmlspecialchars($item['tipo']); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                <td>S/ <?php echo number_format($item['precio'], 2); ?></td>
                <td>
                    <?php if ($item['tipo'] === 'Producto'): ?>
                        <span class="stock <?php echo $item['stock'] <= 10 ? 'stock-bajo' : 'stock-normal'; ?>">
                            <?php echo htmlspecialchars($item['stock']); ?>
                        </span>
                    <?php else: ?>
                        <span class="stock stock-servicio">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/productoservicio/editar/<?php echo (int)$item['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/productoservicio/eliminar/<?php echo (int)$item['id']; ?>" 
                       onclick="return confirm('¿Eliminar producto/servicio?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .btn-nuevo, .btn-productos, .btn-servicios, .btn-stock-bajo {
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
    
    .btn-productos {
        background-color: #007bff;
    }
    
    .btn-servicios {
        background-color: #6f42c1;
    }
    
    .btn-stock-bajo {
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
    
    tr.producto {
        background-color: #e7f3ff;
    }
    
    tr.servicio {
        background-color: #f3e5ff;
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
    
    .stock {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .stock-normal {
        background-color: #28a745;
        color: white;
    }
    
    .stock-bajo {
        background-color: #dc3545;
        color: white;
    }
    
    .stock-servicio {
        background-color: #6c757d;
        color: white;
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
