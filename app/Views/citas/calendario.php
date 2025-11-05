<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center;margin-bottom:30px;">📅 Calendario de Citas Médicas</h1>

<div class="calendario-controls" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;gap:15px;">
    <a href="<?php echo APP_URL; ?>/cita" class="btn-volver">← Volver a Citas</a>
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="<?php echo APP_URL; ?>/cita/calendario?mes=<?php echo $mesAnterior; ?>&año=<?php echo $añoAnterior; ?>" class="btn-nav">◀ Mes Anterior</a>
        <span style="font-size:20px;font-weight:bold;color:#fff;padding:0 15px;"><?php echo htmlspecialchars($mesNombre . ' ' . $año); ?></span>
        <a href="<?php echo APP_URL; ?>/cita/calendario?mes=<?php echo $mesSiguiente; ?>&año=<?php echo $añoSiguiente; ?>" class="btn-nav">Mes Siguiente ▶</a>
    </div>
    <a href="<?php echo APP_URL; ?>/cita/crear" class="btn-nueva">➕ Nueva Cita</a>
</div>

<div class="calendario-wrapper">
    <div class="calendario-header-info">
        <div class="estado-leyenda">
            <span class="leyenda-item">
                <span class="estado-badge estado-pendiente">🐾</span>
                <span>Pendiente</span>
            </span>
            <span class="leyenda-item">
                <span class="estado-badge estado-procesada">✅</span>
                <span>Procesada</span>
            </span>
            <span class="leyenda-item">
                <span class="estado-badge estado-cancelada">❌</span>
                <span>Cancelada</span>
            </span>
        </div>
    </div>
    
    <div class="calendario-grid">
        <div class="calendario-dias-semana">
            <div class="dia-semana">Dom</div>
            <div class="dia-semana">Lun</div>
            <div class="dia-semana">Mar</div>
            <div class="dia-semana">Mié</div>
            <div class="dia-semana">Jue</div>
            <div class="dia-semana">Vie</div>
            <div class="dia-semana">Sáb</div>
        </div>
        
        <div class="calendario-dias">
            <?php 
            // Espacios en blanco para los días antes del primer día del mes
            for ($i = 0; $i < $diaSemanaInicio; $i++): 
            ?>
                <div class="calendario-dia vacio"></div>
            <?php endfor; ?>
            
            <?php 
            // Días del mes
            for ($dia = 1; $dia <= $diasEnMes; $dia++): 
                $tieneCitas = isset($citasPorDia[$dia]);
                $citasDelDia = $tieneCitas ? $citasPorDia[$dia] : [];
                $esHoy = ($dia == date('d') && $mes == date('m') && $año == date('Y'));
            ?>
                <div class="calendario-dia <?php echo $esHoy ? 'hoy' : ''; ?> <?php echo $tieneCitas ? 'tiene-citas' : ''; ?>">
                    <div class="dia-numero"><?php echo $dia; ?></div>
                    <?php if ($tieneCitas): ?>
                        <div class="citas-del-dia">
                            <?php foreach ($citasDelDia as $cita): ?>
                                <div class="cita-mini estado-<?php echo strtolower($cita['estado']); ?>" 
                                     title="<?php echo htmlspecialchars($cita['codigo'] . ' - ' . $cita['mascota_nombre'] . ' - ' . $cita['motivo']); ?>">
                                    <?php 
                                    $fechaObj = new DateTime($cita['fecha_cita']);
                                    echo $fechaObj->format('H:i');
                                    ?>
                                    <span class="cita-mini-texto"><?php echo htmlspecialchars(substr($cita['mascota_nombre'], 0, 8)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Lista detallada de citas del mes -->
<div class="citas-mes-lista" style="margin-top:40px;">
    <h2 style="color:#fff;margin-bottom:20px;text-align:center;">📋 Lista de Citas - <?php echo htmlspecialchars($mesNombre . ' ' . $año); ?></h2>
    
    <?php if (empty($citas)): ?>
        <div class="sin-citas">
            <div class="huellitas-container">
                <span class="huellita">🐾</span>
                <span class="huellita">🐾</span>
                <span class="huellita">🐾</span>
            </div>
            <p>No hay citas programadas para este mes.</p>
            <a href="<?php echo APP_URL; ?>/cita/crear" class="btn-crear-primera">➕ Crear Primera Cita</a>
        </div>
    <?php else: ?>
        <div class="citas-lista-detalle">
            <?php foreach ($citas as $cita): ?>
                <div class="cita-item-detalle estado-<?php echo strtolower($cita['estado']); ?>">
                    <div class="cita-icono">
                        <?php if (strtolower($cita['estado']) == 'pendiente'): ?>
                            🐾
                        <?php elseif (strtolower($cita['estado']) == 'procesada'): ?>
                            ✅
                        <?php else: ?>
                            ❌
                        <?php endif; ?>
                    </div>
                    <div class="cita-fecha-detalle">
                        <?php 
                        $fecha = new DateTime($cita['fecha_cita']);
                        echo '<strong>' . $fecha->format('d/m/Y') . '</strong>';
                        echo '<span class="cita-hora-detalle">' . $fecha->format('H:i') . '</span>';
                        ?>
                    </div>
                    <div class="cita-info-detalle">
                        <div class="cita-codigo-detalle"><?php echo htmlspecialchars($cita['codigo']); ?></div>
                        <div class="cita-mascota-detalle">
                            <span class="icono-mascota">🐕</span>
                            <?php echo htmlspecialchars($cita['mascota_nombre'] ?? 'Sin mascota'); ?>
                        </div>
                        <div class="cita-cliente-detalle">
                            <span class="icono-cliente">👤</span>
                            <?php echo htmlspecialchars(($cita['cliente_nombre'] ?? '') . ' ' . ($cita['cliente_apellido'] ?? '')); ?>
                        </div>
                        <div class="cita-motivo-detalle">
                            <span class="icono-motivo">💬</span>
                            <?php echo htmlspecialchars($cita['motivo']); ?>
                        </div>
                    </div>
                    <div class="cita-acciones-detalle">
                        <span class="estado-badge-detalle estado-<?php echo strtolower($cita['estado']); ?>">
                            <?php echo htmlspecialchars($cita['estado']); ?>
                        </span>
                        <a href="<?php echo APP_URL; ?>/cita/editar/<?php echo (int)$cita['id']; ?>" class="btn-editar-detalle">✏️ Editar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Controles */
.calendario-controls {
    background: #20263B;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.btn-volver, .btn-nueva, .btn-nav {
    display: inline-block;
    padding: 10px 18px;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-volver {
    background-color: #6c757d;
}

.btn-volver:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
}

.btn-nueva {
    background-color: #28a745;
}

.btn-nueva:hover {
    background-color: #218838;
    transform: translateY(-1px);
}

.btn-nav {
    background-color: #2072ff;
    font-size: 14px;
}

.btn-nav:hover {
    background-color: #174b97;
    transform: translateY(-1px);
}

/* Calendario Grid */
.calendario-wrapper {
    background: #20263B;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.calendario-header-info {
    margin-bottom: 20px;
    text-align: center;
}

.estado-leyenda {
    display: flex;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
}

.leyenda-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
}

.estado-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: bold;
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

.calendario-grid {
    background: #141828;
    border-radius: 8px;
    overflow: hidden;
}

.calendario-dias-semana {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #28304A;
    border-bottom: 2px solid #353950;
}

.dia-semana {
    padding: 15px;
    text-align: center;
    font-weight: bold;
    color: #fff;
    font-size: 15px;
}

.calendario-dias {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    background: #353950;
    padding: 2px;
}

.calendario-dia {
    min-height: 100px;
    background: #141828;
    padding: 8px;
    position: relative;
    border-radius: 4px;
    transition: all 0.2s;
}

.calendario-dia.vacio {
    background: #0f121a;
}

.calendario-dia:hover {
    background: #1a1f35;
    transform: scale(1.02);
}

.calendario-dia.hoy {
    background: #2d3748;
    border: 2px solid #2072ff;
}

.calendario-dia.tiene-citas {
    background: #1a2332;
}

.dia-numero {
    font-weight: bold;
    color: #fff;
    font-size: 16px;
    margin-bottom: 5px;
}

.citas-del-dia {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.cita-mini {
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.2s;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.cita-mini.estado-pendiente {
    background-color: rgba(255, 193, 7, 0.3);
    color: #ffc107;
    border-left: 3px solid #ffc107;
}

.cita-mini.estado-procesada {
    background-color: rgba(40, 167, 69, 0.3);
    color: #28a745;
    border-left: 3px solid #28a745;
}

.cita-mini.estado-cancelada {
    background-color: rgba(220, 53, 69, 0.3);
    color: #dc3545;
    border-left: 3px solid #dc3545;
}

.cita-mini:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

.cita-mini-texto {
    display: block;
    font-weight: 600;
    margin-top: 2px;
}

/* Lista de citas detallada */
.sin-citas {
    text-align: center;
    padding: 60px 20px;
    color: #8a95b2;
}

.huellitas-container {
    margin-bottom: 20px;
    font-size: 40px;
    animation: bounce 2s infinite;
}

.huellita {
    display: inline-block;
    margin: 0 10px;
    animation: float 3s ease-in-out infinite;
}

.huellita:nth-child(2) {
    animation-delay: 0.5s;
}

.huellita:nth-child(3) {
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.btn-crear-primera {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 24px;
    background-color: #28a745;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    transition: all 0.2s;
}

.btn-crear-primera:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
}

.citas-lista-detalle {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cita-item-detalle {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #20263B;
    border-radius: 8px;
    border-left: 5px solid;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.cita-item-detalle:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.cita-item-detalle.estado-pendiente {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, #20263B 0%, #2d2a1f 100%);
}

.cita-item-detalle.estado-procesada {
    border-left-color: #28a745;
    background: linear-gradient(135deg, #20263B 0%, #1e2d1f 100%);
}

.cita-item-detalle.estado-cancelada {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #20263B 0%, #2d1e1e 100%);
}

.cita-icono {
    font-size: 35px;
    min-width: 50px;
    text-align: center;
}

.cita-fecha-detalle {
    min-width: 120px;
    text-align: center;
    color: #fff;
}

.cita-hora-detalle {
    display: block;
    font-size: 13px;
    color: #8a95b2;
    margin-top: 4px;
}

.cita-info-detalle {
    flex: 1;
}

.cita-codigo-detalle {
    font-weight: bold;
    color: #2072ff;
    font-size: 16px;
    margin-bottom: 8px;
}

.cita-mascota-detalle,
.cita-cliente-detalle,
.cita-motivo-detalle {
    color: #fff;
    margin: 5px 0;
    font-size: 14px;
}

.icono-mascota,
.icono-cliente,
.icono-motivo {
    margin-right: 8px;
    font-size: 16px;
}

.cita-acciones-detalle {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
    min-width: 120px;
}

.estado-badge-detalle {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.estado-badge-detalle.estado-pendiente {
    background-color: #ffc107;
    color: #000;
}

.estado-badge-detalle.estado-procesada {
    background-color: #28a745;
    color: #fff;
}

.estado-badge-detalle.estado-cancelada {
    background-color: #dc3545;
    color: #fff;
}

.btn-editar-detalle {
    padding: 8px 16px;
    background-color: #17a2b8;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-editar-detalle:hover {
    background-color: #138496;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

@media (max-width: 768px) {
    .calendario-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .cita-item-detalle {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .calendario-dia {
        min-height: 80px;
    }
    
    .cita-mini {
        font-size: 10px;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
