<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="calendario-controls">
    <a href="<?php echo APP_URL; ?>/cita" class="btn-volver">← Volver a Citas</a>
    <a href="<?php echo APP_URL; ?>/cita/crear" class="btn-nueva">Nueva Cita</a>
</div>

<div class="calendario-container">
    <div class="calendario-header">
        <h2>Citas del Mes</h2>
        <div class="estado-leyenda">
            <span class="leyenda-item">
                <span class="estado-badge estado-pendiente"></span> Pendiente
            </span>
            <span class="leyenda-item">
                <span class="estado-badge estado-procesada"></span> Procesada
            </span>
            <span class="leyenda-item">
                <span class="estado-badge estado-cancelada"></span> Cancelada
            </span>
        </div>
    </div>
    
    <div class="citas-lista">
        <?php if (empty($citas)): ?>
            <div class="sin-citas">
                <p>No hay citas programadas para este mes.</p>
                <a href="<?php echo APP_URL; ?>/cita/crear" class="btn-crear-primera">Crear Primera Cita</a>
            </div>
        <?php else: ?>
            <?php foreach ($citas as $cita): ?>
                <div class="cita-item estado-<?php echo strtolower($cita['estado']); ?>">
                    <div class="cita-fecha">
                        <?php 
                        $fecha = new DateTime($cita['fecha_cita']);
                        echo $fecha->format('d/m/Y');
                        ?>
                        <span class="cita-hora"><?php echo $fecha->format('H:i'); ?></span>
                    </div>
                    <div class="cita-info">
                        <div class="cita-codigo"><?php echo htmlspecialchars($cita['codigo']); ?></div>
                        <div class="cita-mascota"><?php echo htmlspecialchars($cita['mascota_nombre']); ?></div>
                        <div class="cita-cliente"><?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?></div>
                        <div class="cita-motivo"><?php echo htmlspecialchars($cita['motivo']); ?></div>
                    </div>
                    <div class="cita-acciones">
                        <span class="estado-badge estado-<?php echo strtolower($cita['estado']); ?>">
                            <?php echo htmlspecialchars($cita['estado']); ?>
                        </span>
                        <a href="<?php echo APP_URL; ?>/cita/editar/<?php echo (int)$cita['id']; ?>" class="btn-editar">Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .calendario-controls {
        margin-bottom: 20px;
    }
    
    .btn-volver, .btn-nueva {
        display: inline-block;
        padding: 8px 15px;
        margin-right: 10px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .btn-volver {
        background-color: #6c757d;
    }
    
    .btn-nueva {
        background-color: #28a745;
    }
    
    .calendario-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .calendario-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .calendario-header h2 {
        margin: 0;
        color: #495057;
    }
    
    .estado-leyenda {
        display: flex;
        gap: 15px;
    }
    
    .leyenda-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
    }
    
    .citas-lista {
        padding: 20px;
    }
    
    .sin-citas {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .btn-crear-primera {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    .cita-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        border-left: 4px solid;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .cita-item.estado-pendiente {
        border-left-color: #ffc107;
        background-color: #fff3cd;
    }
    
    .cita-item.estado-procesada {
        border-left-color: #28a745;
        background-color: #d4edda;
    }
    
    .cita-item.estado-cancelada {
        border-left-color: #dc3545;
        background-color: #f8d7da;
    }
    
    .cita-fecha {
        min-width: 120px;
        text-align: center;
        font-weight: bold;
        color: #495057;
    }
    
    .cita-hora {
        display: block;
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }
    
    .cita-info {
        flex: 1;
        margin: 0 20px;
    }
    
    .cita-codigo {
        font-weight: bold;
        color: #007bff;
        font-size: 14px;
    }
    
    .cita-mascota {
        font-weight: bold;
        color: #495057;
        margin: 2px 0;
    }
    
    .cita-cliente {
        color: #6c757d;
        font-size: 14px;
        margin: 2px 0;
    }
    
    .cita-motivo {
        color: #495057;
        font-size: 13px;
        margin-top: 5px;
        font-style: italic;
    }
    
    .cita-acciones {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 8px;
    }
    
    .estado-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
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
    
    .btn-editar {
        padding: 4px 8px;
        background-color: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .btn-editar:hover {
        background-color: #138496;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
