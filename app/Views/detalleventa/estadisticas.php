<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="actions">
    <a href="<?php echo APP_URL; ?>/detalleventa" class="btn-volver">Volver a Detalles de Venta</a>
</div>

<div class="stats-summary">
    <h2>Resumen de Estadísticas</h2>
    <div class="summary-grid">
        <div class="summary-item">
            <strong>Total de Productos/Servicios:</strong> <?php echo count($estadisticas); ?>
        </div>
        <div class="summary-item">
            <strong>Productos más vendidos:</strong> <?php echo count($estadisticas) > 0 ? $estadisticas[0]['producto_nombre'] : 'N/A'; ?>
        </div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Producto/Servicio</th>
            <th>Tipo</th>
            <th>Total Vendido</th>
            <th>Total Ingresos</th>
            <th>Veces Vendido</th>
            <th>Promedio por Venta</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($estadisticas)): ?>
            <tr>
                <td colspan="6" class="no-data">No hay estadísticas disponibles.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($estadisticas as $stat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($stat['producto_nombre']); ?></td>
                    <td>
                        <span class="tipo-badge tipo-<?php echo strtolower($stat['producto_tipo']); ?>">
                            <?php echo htmlspecialchars($stat['producto_tipo']); ?>
                        </span>
                    </td>
                    <td><?php echo (int)$stat['total_vendido']; ?></td>
                    <td>S/ <?php echo number_format($stat['total_ingresos'], 2); ?></td>
                    <td><?php echo (int)$stat['veces_vendido']; ?></td>
                    <td>S/ <?php echo number_format($stat['total_ingresos'] / max($stat['veces_vendido'], 1), 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
.actions {
    margin-bottom: 20px;
}

.btn-volver {
    background-color: #6c757d;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}

.btn-volver:hover {
    background-color: #5a6268;
}

.stats-summary {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.stats-summary h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.summary-item {
    background-color: white;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

th {
    background-color: #f8f9fa;
    font-weight: bold;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

tr:hover {
    background-color: #e9ecef;
}

.no-data {
    text-align: center;
    font-style: italic;
    color: #6c757d;
}

.tipo-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.tipo-producto {
    background-color: #d4edda;
    color: #155724;
}

.tipo-servicio {
    background-color: #cce5ff;
    color: #004085;
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
