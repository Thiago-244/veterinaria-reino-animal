<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<div class="mb-4 flex-wrap gap-2" style="display: flex; flex-wrap: wrap; gap: 8px;">
  <a href="<?php echo APP_URL; ?>/venta/crear" class="btn btn--success">Nueva Venta</a>
  <a href="<?php echo APP_URL; ?>/venta/ventas-del-dia" class="btn btn--info">Ventas del Día</a>
  <a href="<?php echo APP_URL; ?>/venta/reportes" class="btn btn--ghost" style="background: #7c3aed; color: #fff;">Reportes</a>
</div>

<table class="table">
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
      <td><span class="badge badge--success">S/ <?php echo number_format($venta['total'], 2); ?></span></td>
      <td>
        <a href="<?php echo APP_URL; ?>/venta/ver/<?php echo (int)$venta['id']; ?>" class="badge badge--info">Ver</a>
        <a href="<?php echo APP_URL; ?>/venta/eliminar/<?php echo (int)$venta['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar venta?');">Eliminar</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
