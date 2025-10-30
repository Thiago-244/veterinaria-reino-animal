<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/especie/crear" class="btn btn--success mb-3">Nueva Especie</a>

<table class="table">
  <thead>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Total Razas</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($especies as $especie): ?>
    <tr>
      <td><?php echo htmlspecialchars($especie['id']); ?></td>
      <td><?php echo htmlspecialchars($especie['especie_nombre']); ?></td>
      <td><span class="badge badge--info"><?php echo htmlspecialchars($especie['total_razas']); ?></span></td>
      <td>
        <a href="<?php echo APP_URL; ?>/especie/editar/<?php echo (int)$especie['id']; ?>" class="badge badge--info">Editar</a>
        <a href="<?php echo APP_URL; ?>/especie/eliminar/<?php echo (int)$especie['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar especie?');">Eliminar</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
