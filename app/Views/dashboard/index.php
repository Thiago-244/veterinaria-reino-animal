<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="dashboard-container">
    <!-- Estadísticas Generales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3><?php echo $estadisticas['clientes']['total_clientes']; ?></h3>
                <p>Clientes Registrados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🐾</div>
            <div class="stat-content">
                <h3><?php echo $estadisticas['clientes']['total_mascotas']; ?></h3>
                <p>Mascotas Registradas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3><?php
                    $ventasStats = $estadisticas['ventas'] ?? [];
                    $ventasFila = is_array($ventasStats) && isset($ventasStats[0]) ? $ventasStats[0] : $ventasStats;
                    echo htmlspecialchars((string)($ventasFila['total_ventas'] ?? 0));
                ?></h3>
                <p>Ventas Realizadas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3><?php echo $estadisticas['productos']['total_productos']; ?></h3>
                <p>Productos/Servicios</p>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="dashboard-content">
        <div class="dashboard-row">
            <!-- Ventas del Día -->
            <div class="dashboard-card">
                <h2>Ventas del Día</h2>
                <?php if (empty($ventas_del_dia)): ?>
                    <p class="no-data">No hay ventas registradas hoy.</p>
                <?php else: ?>
                    <div class="ventas-list">
                        <?php foreach ($ventas_del_dia as $venta): ?>
                            <div class="venta-item">
                                <div class="venta-info">
                                    <strong><?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?></strong>
                                    <span class="venta-total">S/ <?php echo number_format($venta['total'], 2); ?></span>
                                </div>
                                <div class="venta-meta">
                                    <?php echo date('H:i', strtotime($venta['creado_en'])); ?> - 
                                    <?php echo htmlspecialchars($venta['usuario_nombre']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Clientes Recientes -->
            <div class="dashboard-card">
                <h2>Clientes Recientes</h2>
                <?php if (empty($clientes_recientes)): ?>
                    <p class="no-data">No hay clientes registrados.</p>
                <?php else: ?>
                    <div class="clientes-list">
                        <?php foreach ($clientes_recientes as $cliente): ?>
                            <div class="cliente-item">
                                <div class="cliente-info">
                                    <strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong>
                                    <span class="cliente-dni"><?php echo htmlspecialchars($cliente['dni']); ?></span>
                                </div>
                                <div class="cliente-meta">
                                    <?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-row">
            <!-- Productos con Stock Bajo -->
            <div class="dashboard-card">
                <h2>Stock Bajo</h2>
                <?php if (empty($productos_stock_bajo)): ?>
                    <p class="no-data">Todos los productos tienen stock suficiente.</p>
                <?php else: ?>
                    <div class="productos-list">
                        <?php foreach ($productos_stock_bajo as $producto): ?>
                            <div class="producto-item stock-bajo">
                                <div class="producto-info">
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    <span class="producto-stock">Stock: <?php echo $producto['stock']; ?></span>
                                </div>
                                <div class="producto-meta">
                                    <?php echo htmlspecialchars($producto['tipo']); ?> - 
                                    S/ <?php echo number_format($producto['precio'], 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Citas Próximas -->
            <div class="dashboard-card">
                <h2>Citas Próximas</h2>
                <?php if (empty($citas_proximas)): ?>
                    <p class="no-data">No hay citas programadas.</p>
                <?php else: ?>
                    <div class="citas-list">
                        <?php foreach ($citas_proximas as $cita): ?>
                            <div class="cita-item">
                                <div class="cita-info">
                                    <strong><?php echo htmlspecialchars($cita['mascota_nombre']); ?></strong>
                                    <span class="cita-fecha"><?php echo date('d/m/Y H:i', strtotime($cita['fecha_cita'])); ?></span>
                                </div>
                                <div class="cita-meta">
                                    <?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?> - 
                                    <?php echo htmlspecialchars($cita['motivo']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div class="actions-grid">
            <a href="<?php echo APP_URL; ?>/cliente/crear" class="action-btn">
                <span class="action-icon">➕</span>
                <span>Nuevo Cliente</span>
            </a>
            <a href="<?php echo APP_URL; ?>/venta/crear" class="action-btn">
                <span class="action-icon">💰</span>
                <span>Nueva Venta</span>
            </a>
            <a href="<?php echo APP_URL; ?>/cita/crear" class="action-btn">
                <span class="action-icon">📅</span>
                <span>Nueva Cita</span>
            </a>
            <a href="<?php echo APP_URL; ?>/productoservicio/crear" class="action-btn">
                <span class="action-icon">📦</span>
                <span>Nuevo Producto</span>
            </a>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 2.5em;
    opacity: 0.8;
}

.stat-content h3 {
    margin: 0;
    font-size: 2em;
    font-weight: bold;
}

.stat-content p {
    margin: 5px 0 0 0;
    opacity: 0.9;
    font-size: 0.9em;
}

.dashboard-content {
    margin-bottom: 30px;
}

.dashboard-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.dashboard-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}

.dashboard-card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
    font-size: 1.2em;
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 10px;
}

.venta-item, .cliente-item, .producto-item, .cita-item {
    padding: 12px;
    border-bottom: 1px solid #f8f9fa;
    margin-bottom: 8px;
}

.venta-item:last-child, .cliente-item:last-child, .producto-item:last-child, .cita-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.venta-info, .cliente-info, .producto-info, .cita-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.venta-total, .producto-stock {
    font-weight: bold;
    color: #28a745;
}

.cliente-dni, .cita-fecha {
    color: #6c757d;
    font-size: 0.9em;
}

.venta-meta, .cliente-meta, .producto-meta, .cita-meta {
    font-size: 0.85em;
    color: #6c757d;
}

.stock-bajo {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 4px;
}

.no-data {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 20px;
}

.quick-actions {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #e1e5e9;
}

.quick-actions h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    text-decoration: none;
    color: #333;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.action-btn:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
    transform: translateY(-2px);
}

.action-icon {
    font-size: 2em;
    margin-bottom: 8px;
}

.action-btn span:last-child {
    font-size: 0.9em;
    font-weight: 500;
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
