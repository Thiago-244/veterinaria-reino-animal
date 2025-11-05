<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<?php if (!empty($_SESSION['success_message'])): ?>
  <div class="alert-success" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;">
    <?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['error_message'])): ?>
  <div class="alert-error" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;">
    <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
  </div>
<?php endif; ?>

<div class="mb-4" style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
  <form method="GET" action="" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <input type="text" name="buscar" value="<?php echo isset($buscar)?htmlspecialchars($buscar):''; ?>" 
           placeholder="Buscar por cliente (nombre, DNI)..." 
           style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;min-width:280px;background:#151b2b;color:#fff;margin-right:3px;">
    <input type="date" name="fecha" value="<?php echo isset($fecha)?htmlspecialchars($fecha):''; ?>" 
           placeholder="Buscar por fecha..." 
           style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;background:#151b2b;color:#fff;">
    <button type="submit" class="btn--buscar">🔍 Buscar</button>
    <?php if (!empty($buscar) || !empty($fecha)) : ?>
      <a href="<?php echo APP_URL; ?>/venta" style="font-size:14px;margin-left:8px;color:#bd2130;background:none;border:none;text-decoration:underline;">Limpiar</a>
    <?php endif; ?>
  </form>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?php echo APP_URL; ?>/venta/crear" class="btn btn--success">➕ Nueva Venta</a>
    <a href="<?php echo APP_URL; ?>/venta/ventas-del-dia" class="btn btn--info">📅 Ventas del Día</a>
    <a href="<?php echo APP_URL; ?>/venta/reportes" class="btn btn--ghost" style="background: #7c3aed; color: #fff;">📊 Reportes</a>
  </div>
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
    <?php if (empty($ventas)): ?>
      <tr>
        <td colspan="6" style="text-align:center;padding:40px;color:#6c757d;">
          <?php if (!empty($buscar) || !empty($fecha)): ?>
            No se encontraron ventas con los criterios de búsqueda.
          <?php else: ?>
            No hay ventas registradas.
          <?php endif; ?>
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($ventas as $venta): ?>
      <tr>
        <td><?php echo htmlspecialchars($venta['id']); ?></td>
        <td><?php echo date('d/m/Y H:i', strtotime($venta['creado_en'])); ?></td>
        <td>
          <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?>
          <br><small style="color:#6c757d;">(<?php echo htmlspecialchars($venta['cliente_dni']); ?>)</small>
        </td>
        <td><?php echo htmlspecialchars($venta['usuario_nombre'] ?? 'N/A'); ?></td>
        <td><span class="badge badge--success">S/ <?php echo number_format($venta['total'], 2); ?></span></td>
        <td>
          <a href="<?php echo APP_URL; ?>/venta/ver/<?php echo (int)$venta['id']; ?>" class="badge badge--info">👁️ Ver</a>
          <a href="<?php echo APP_URL; ?>/venta/eliminar/<?php echo (int)$venta['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Estás seguro de eliminar esta venta?');">🗑️ Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>

<style>
.btn--success { 
  background: #28a745; 
  color:#fff !important; 
  border-radius:7px; 
  padding:10px 18px; 
  text-decoration:none; 
  font-weight:600; 
  transition: all 0.2s;
}
.btn--success:hover { 
  background:#218838; 
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}
.btn--info { 
  background:#17a2b8; 
  color:#fff !important; 
  border-radius:7px; 
  padding:10px 18px; 
  text-decoration:none; 
  font-weight:600; 
  transition: all 0.2s;
}
.btn--info:hover { 
  background:#138496; 
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
}
.btn--buscar { 
  background:#2072ff; 
  color:#fff; 
  border:none; 
  border-radius:6px; 
  font-size:15.5px;
  font-weight:600;
  padding:9px 18px; 
  cursor:pointer; 
  transition:.2s;
}
.btn--buscar:hover { 
  background:#174b97;
  transform: translateY(-1px);
}
.alert-success { 
  background: #d1e7dd; 
  color:#0f5132; 
  border:1px solid #badbcc; 
  border-radius:4px; 
  padding:9px 20px; 
  margin-bottom:13px; 
  font-size:16px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.alert-error {
  background: #f8d7da;
  color: #842029;
  border: 1px solid #f5c2c7;
  border-radius: 4px;
  padding: 9px 20px;
  margin-bottom: 13px;
  font-size: 16px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.badge--success {
  background: #28a745;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
}
.badge--info {
  background: #17a2b8;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  text-decoration: none;
  font-size: 13px;
  margin-right: 5px;
  transition: all 0.2s;
}
.badge--info:hover {
  background: #138496;
  transform: translateY(-1px);
}
.badge--danger {
  background: #dc3545;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  text-decoration: none;
  font-size: 13px;
  transition: all 0.2s;
}
.badge--danger:hover {
  background: #b02a37;
  transform: translateY(-1px);
}
</style>
