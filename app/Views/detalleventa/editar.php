<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<div class="alert-container">
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
</div>

<div class="venta-info">
    <h2>Información de la Venta</h2>
    <div class="info-grid">
        <div class="info-item">
            <strong>ID Venta:</strong> <?php echo (int)$venta['id']; ?>
        </div>
        <div class="info-item">
            <strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?>
        </div>
        <div class="info-item">
            <strong>Usuario:</strong> <?php echo htmlspecialchars($venta['usuario_nombre']); ?>
        </div>
        <div class="info-item">
            <strong>Total Actual:</strong> S/ <?php echo number_format($venta['total'], 2); ?>
        </div>
    </div>
</div>

<form method="POST" action="<?php echo APP_URL; ?>/detalleventa/editar/<?php echo (int)$id; ?>" class="form-container">
    <div class="form-group">
        <label for="id_producto">Producto/Servicio *</label>
        <select name="id_producto" id="id_producto" required>
            <option value="">Seleccionar producto/servicio</option>
            <?php foreach ($productos as $producto): ?>
                <option value="<?php echo (int)$producto['id']; ?>" 
                        data-precio="<?php echo $producto['precio']; ?>"
                        <?php echo ($id_producto == $producto['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($producto['nombre'] . ' (' . $producto['tipo'] . ') - S/ ' . number_format($producto['precio'], 2)); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="cantidad">Cantidad *</label>
        <input type="number" name="cantidad" id="cantidad" min="1" step="1" value="<?php echo htmlspecialchars($cantidad); ?>" required>
    </div>

    <div class="form-group">
        <label for="precio_unitario">Precio Unitario *</label>
        <input type="number" name="precio_unitario" id="precio_unitario" min="0.01" step="0.01" value="<?php echo htmlspecialchars($precio_unitario); ?>" required>
    </div>

    <div class="form-group">
        <label for="subtotal">Subtotal</label>
        <input type="number" name="subtotal" id="subtotal" min="0.01" step="0.01" value="<?php echo htmlspecialchars($subtotal); ?>" readonly>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-submit">Actualizar Detalle</button>
        <a href="<?php echo APP_URL; ?>/detalleventa/por-venta/<?php echo (int)$venta['id']; ?>" class="btn-cancel">Cancelar</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productoSelect = document.getElementById('id_producto');
    const cantidadInput = document.getElementById('cantidad');
    const precioInput = document.getElementById('precio_unitario');
    const subtotalInput = document.getElementById('subtotal');

    function calcularSubtotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio = parseFloat(precioInput.value) || 0;
        const subtotal = cantidad * precio;
        subtotalInput.value = subtotal.toFixed(2);
    }

    // Cambiar precio cuando se selecciona un producto
    productoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const precio = selectedOption.getAttribute('data-precio');
        if (precio) {
            precioInput.value = precio;
            calcularSubtotal();
        }
    });

    // Calcular subtotal cuando cambian cantidad o precio
    cantidadInput.addEventListener('input', calcularSubtotal);
    precioInput.addEventListener('input', calcularSubtotal);

    // Calcular subtotal inicial
    calcularSubtotal();
});
</script>

<style>
.alert-container {
    margin: 20px 0;
}

.alert {
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.venta-info {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.venta-info h2 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    background-color: white;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.form-container {
    background-color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 0 auto;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.form-group input[readonly] {
    background-color: #f8f9fa;
    color: #6c757d;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
}

.btn-submit {
    background-color: #007bff;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.btn-submit:hover {
    background-color: #0056b3;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
    text-align: center;
}

.btn-cancel:hover {
    background-color: #5a6268;
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
