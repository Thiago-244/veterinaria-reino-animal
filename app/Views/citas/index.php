<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/cita/crear" class="btn-nuevo">Nueva Cita Médica</a>
<a href="<?php echo APP_URL; ?>/cita/calendario" class="btn-calendario">Ver Calendario</a>

<table>
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
            <tr class="estado-<?php echo strtolower($cita['estado']); ?>">
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
                    <span class="estado-badge estado-<?php echo strtolower($cita['estado']); ?>">
                        <?php echo htmlspecialchars($cita['estado']); ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/cita/editar/<?php echo (int)$cita['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/cita/eliminar/<?php echo (int)$cita['id']; ?>" onclick="return confirm('¿Eliminar cita?');">Eliminar</a>
                    |
                    <form method="POST" action="<?php echo APP_URL; ?>/cita/cambiar-estado/<?php echo (int)$cita['id']; ?>" style="display: inline;">
                        <select name="estado" onchange="this.form.submit()" class="estado-select">
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

<style>
    .btn-nuevo, .btn-calendario {
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
    
    .btn-calendario {
        background-color: #17a2b8;
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
    
    .estado-pendiente {
        background-color: #fff3cd;
    }
    
    .estado-procesada {
        background-color: #d4edda;
    }
    
    .estado-cancelada {
        background-color: #f8d7da;
    }
    
    .estado-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .estado-badge.estado-pendiente {
        background-color: #ffc107;
        color: #000;
    }
    
    .estado-badge.estado-procesada {
        background-color: #28a745;
        color: #fff;
    }
    
    .estado-badge.estado-cancelada {
        background-color: #dc3545;
        color: #fff;
    }
    
    .estado-select {
        padding: 2px 4px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 12px;
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
