<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/usuario/crear" class="btn btn--success mb-3">Nuevo Usuario</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td>
                    <?php
                    $rol = strtolower($usuario['rol']);
                    $badgeRol = 'badge--role-' . ($rol === 'administrador' ? 'admin' : ($rol === 'editor' ? 'editor' : 'consultor'));
                    ?>
                    <span class="badge <?php echo $badgeRol; ?>">
                        <?php echo htmlspecialchars($usuario['rol']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo $usuario['estado'] ? 'badge--success' : 'badge--danger'; ?>">
                        <?php echo $usuario['estado'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </td>
                <td style="min-width:180px;">
                    <a href="<?php echo APP_URL; ?>/usuario/editar/<?php echo (int)$usuario['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/usuario/eliminar/<?php echo (int)$usuario['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
                    <form method="POST" action="<?php echo APP_URL; ?>/usuario/cambiar-estado/<?php echo (int)$usuario['id']; ?>" style="display:inline;">
                        <button type="submit" class="btn btn--ghost badge" style="margin-left:6px;">
                            <?php echo $usuario['estado'] ? 'Desactivar' : 'Activar'; ?>
                        </button>
                        <input type="hidden" name="estado" value="<?php echo $usuario['estado'] ? 0 : 1; ?>">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
