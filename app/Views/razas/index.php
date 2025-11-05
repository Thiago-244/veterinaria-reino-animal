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
           placeholder="Buscar por nombre de raza..." 
           style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;min-width:250px;background:#151b2b;color:#fff;">
    <select name="especie" style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;background:#151b2b;color:#fff;min-width:200px;">
      <option value="">Todas las especies</option>
      <?php foreach ($especies as $esp): ?>
        <option value="<?php echo (int)$esp['id']; ?>" <?php echo (isset($id_especie) && $id_especie == $esp['id']) ? 'selected' : ''; ?>>
          <?php echo htmlspecialchars($esp['nombre']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn--buscar">🔍 Buscar</button>
    <?php if (!empty($buscar) || !empty($id_especie)) : ?>
      <a href="<?php echo APP_URL; ?>/raza" style="font-size:14px;margin-left:8px;color:#bd2130;background:none;border:none;text-decoration:underline;">Limpiar</a>
    <?php endif; ?>
  </form>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?php echo APP_URL; ?>/raza/crear" class="btn btn--success">➕ Nueva Raza</a>
  </div>
</div>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Especie</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($razas)): ?>
      <tr>
        <td colspan="4" style="text-align:center;padding:40px;color:#6c757d;">
          <?php if (!empty($buscar) || !empty($id_especie)): ?>
            No se encontraron razas con los criterios de búsqueda.
          <?php else: ?>
            No hay razas registradas.
          <?php endif; ?>
        </td>
      </tr>
    <?php else: ?>
      <?php foreach ($razas as $raza): ?>
      <tr>
        <td><?php echo htmlspecialchars($raza['id']); ?></td>
        <td><strong><?php echo htmlspecialchars($raza['nombre']); ?></strong></td>
        <td>
          <span class="badge badge--info">
            <?php echo htmlspecialchars($raza['especie_nombre'] ?? 'N/A'); ?>
          </span>
        </td>
        <td>
          <a href="<?php echo APP_URL; ?>/raza/editar/<?php echo (int)$raza['id']; ?>" class="badge badge--info">✏️ Editar</a>
          <a href="<?php echo APP_URL; ?>/raza/eliminar/<?php echo (int)$raza['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Estás seguro de eliminar esta raza?');">🗑️ Eliminar</a>
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
select {
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 35px;
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
.badge--info {
  background: #17a2b8;
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
}
.badge--danger {
  background: #dc3545;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  text-decoration: none;
  font-size: 13px;
  margin-left: 5px;
  transition: all 0.2s;
}
.badge--danger:hover {
  background: #b02a37;
  transform: translateY(-1px);
}
a.badge--info {
  text-decoration: none;
  transition: all 0.2s;
}
a.badge--info:hover {
  background: #138496;
  transform: translateY(-1px);
}
</style>
