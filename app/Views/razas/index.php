<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/raza/crear" class="btn btn--success mb-3">Nueva Raza</a>

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
    <?php foreach ($razas as $raza): ?>
    <tr>
      <td><?php echo htmlspecialchars($raza['id']); ?></td>
      <td><?php echo htmlspecialchars($raza['nombre']); ?></td>
      <td><span class="badge badge--info"><?php echo htmlspecialchars($raza['especie_nombre']); ?></span></td>
      <td>
        <a href="<?php echo APP_URL; ?>/raza/editar/<?php echo (int)$raza['id']; ?>" class="badge badge--info">Editar</a>
        <a href="<?php echo APP_URL; ?>/raza/eliminar/<?php echo (int)$raza['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar raza?');">Eliminar</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
