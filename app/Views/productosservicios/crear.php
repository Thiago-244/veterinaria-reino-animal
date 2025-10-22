<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form method="POST" action="<?php echo APP_URL; ?>/productoservicio/guardar">
    <div class="form-group">
        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required onchange="toggleStock()">
            <option value="">Seleccionar tipo...</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>">
                    <?php echo htmlspecialchars($tipo); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" 
               placeholder="Ej: Alimento Premium para Perros">
    </div>
    
    <div class="form-group">
        <label for="precio">Precio (S/):</label>
        <input type="number" id="precio" name="precio" required min="0.01" step="0.01" 
               placeholder="0.00">
    </div>
    
    <div class="form-group" id="stock-group">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" min="0" value="0" 
               placeholder="0">
        <small class="help-text">Para servicios, el stock se establecerá automáticamente en 9999</small>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Guardar Producto/Servicio</button>
        <a href="<?php echo APP_URL; ?>/productoservicio" class="btn-cancelar">Cancelar</a>
    </div>
</form>

<script>
function toggleStock() {
    const tipo = document.getElementById('tipo').value;
    const stockGroup = document.getElementById('stock-group');
    const stockInput = document.getElementById('stock');
    
    if (tipo === 'Servicio') {
        stockGroup.style.display = 'none';
        stockInput.value = 9999;
    } else {
        stockGroup.style.display = 'block';
        stockInput.value = 0;
    }
}
</script>

<style>
    .form-group {
        margin-bottom: 20px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }
    
    input[type="text"], input[type="number"], select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        max-width: 400px;
    }
    
    input:focus, select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }
    
    .help-text {
        display: block;
        margin-top: 5px;
        color: #6c757d;
        font-size: 14px;
    }
    
    .form-actions {
        margin-top: 30px;
    }
    
    .btn-guardar {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-right: 10px;
    }
    
    .btn-guardar:hover {
        background-color: #218838;
    }
    
    .btn-cancelar {
        background-color: #6c757d;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 16px;
        display: inline-block;
    }
    
    .btn-cancelar:hover {
        background-color: #5a6268;
        text-decoration: none;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
