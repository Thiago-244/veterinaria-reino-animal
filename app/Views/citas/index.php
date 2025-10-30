<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<div class="mb-4 flex-wrap gap-2" style="display: flex; flex-wrap: wrap; gap: 8px;">
    <a href="<?php echo APP_URL; ?>/cita/crear" class="btn btn--success">Nueva Cita Médica</a>
    <a href="<?php echo APP_URL; ?>/cita/calendario" class="btn btn--info">Ver Calendario</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Fecha y Hora</th>
            <th>Mascota</th>
            <th>Cliente</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $cita): ?>
            <tr>
                <td><?php echo htmlspecialchars($cita['codigo']); ?></td>
                <td>
                    <?php 
                    $fecha = new DateTime($cita['fecha_cita']);
                    echo $fecha->format('d/m/Y H:i');
                    ?>
                </td>
                <td><?php echo htmlspecialchars($cita['mascota_nombre'] . ' (' . $cita['mascota_codigo'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?></td>
                <td><?php echo htmlspecialchars($cita['motivo']); ?></td>
                <td>
                    <?php
                    $est = strtolower($cita['estado']);
                    $badgeEstado = $est === 'pendiente'   ? 'badge--warn'
                                : ($est === 'procesada'   ? 'badge--success' : 'badge--danger');
                    ?>
                    <span class="badge <?php echo $badgeEstado; ?>"><?php echo htmlspecialchars($cita['estado']); ?></span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/cita/editar/<?php echo (int)$cita['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/cita/eliminar/<?php echo (int)$cita['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar cita?');">Eliminar</a>
                    <form method="POST" action="<?php echo APP_URL; ?>/cita/cambiar-estado/<?php echo (int)$cita['id']; ?>" style="display: inline;">
                        <select name="estado" onchange="this.form.submit()" class="badge" style="min-width: 98px; padding: 4px 9px; border-radius: 7px; font-size: 13.5px; margin-left: 7px;">
                            <option value="Pendiente" <?php echo ($cita['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Procesada" <?php echo ($cita['estado'] == 'Procesada') ? 'selected' : ''; ?>>Procesada</option>
                            <option value="Cancelada" <?php echo ($cita['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
