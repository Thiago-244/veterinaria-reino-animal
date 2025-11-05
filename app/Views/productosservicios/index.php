<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<?php if (!empty($_SESSION['success_message'])): ?>
  <div class="alert-success" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;"> <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?> </div>
<?php endif; ?>
<div class="mb-4" style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
  <form method="GET" action="" style="display:flex;align-items:center;gap:10px;">
    <input type="text" name="buscar" value="<?php echo isset($buscar)?htmlspecialchars($buscar):''; ?>" placeholder="Buscar por nombre o tipo..." style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;min-width:280px;background:#151b2b;color:#fff;margin-right:3px;">
    <button type="submit" class="btn--buscar">Buscar</button>
    <?php if (!empty($buscar)) : ?>
      <a href="<?php echo APP_URL; ?>/productoservicio" style="font-size:14px;margin-left:8px;color:#bd2130;background:none;border:none;text-decoration:underline;">Limpiar</a>
    <?php endif; ?>
  </form>
  <div style="display:flex;gap:8px;">
    <a href="<?php echo APP_URL; ?>/productoservicio/crear" class="btn btn--success">Nuevo Producto/Servicio</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/productos" class="btn btn--info">Solo Productos</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/servicios" class="btn btn--ghost" style="background: #7c3aed; color: #fff;">Solo Servicios</a>
    <a href="<?php echo APP_URL; ?>/productoservicio/stock-bajo" class="btn btn--danger">Stock Bajo</a>
  </div>
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

<style>
.btn--success { background: #28a745; color:#fff !important; border-radius:7px; padding:10px 18px; text-decoration:none; font-weight:600; }
.btn--success:hover { background:#218838; }
.btn--info { background:#17a2b8; color:#fff !important; border-radius:7px; padding:10px 18px; text-decoration:none; font-weight:600; }
.btn--info:hover { background:#138496; }
.btn--danger { background:#dc3545; color:#fff !important; border-radius:7px; padding:10px 18px; text-decoration:none; font-weight:600; }
.btn--danger:hover { background:#b02a37; }
.btn--buscar { background:#2072ff; color:#fff; border:none; border-radius:6px; font-size:15.5px;font-weight:600;padding:9px 18px; cursor:pointer; transition:.13s}
.btn--buscar:hover { background:#174b97;}
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:9px 20px; margin-bottom:13px; font-size:16px;}
</style>
