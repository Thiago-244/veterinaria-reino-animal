<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<div class="mb-4 flex-wrap gap-2" style="display: flex; flex-wrap: wrap; gap: 8px;">
    <a href="<?php echo APP_URL; ?>/productoservicio/crear" class="btn btn--success">Nuevo Producto/Servicio</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/productos" class="btn btn--info">Solo Productos</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/servicios" class="btn btn--ghost" style="background: #7c3aed; color: #fff;">Solo Servicios</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/stock-bajo" class="btn btn--danger">Stock Bajo</a>
</div>

<table class="table">
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
            <tr class="tr-<?php echo strtolower($item['tipo']); ?>">
                <td><?php echo htmlspecialchars($item['id']); ?></td>
                <td>
                    <span class="badge badge--<?php echo strtolower($item['tipo']) === 'producto' ? 'info' : 'role-editor'; ?>">
                        <?php echo htmlspecialchars($item['tipo']); ?> 
                    </span>
                </td>
                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                <td>S/ <?php echo number_format($item['precio'], 2); ?></td>
                <td>
                    <?php if ($item['tipo'] === 'Producto'): ?>
                        <span class="badge <?php echo $item['stock'] <= 10 ? 'badge--warn' : 'badge--success'; ?>">
                            <?php echo htmlspecialchars($item['stock']); ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge--dark">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/productoservicio/editar/<?php echo (int)$item['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/productoservicio/eliminar/<?php echo (int)$item['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar producto/servicio?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
