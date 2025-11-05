<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Editar Producto/Servicio</h1>
<?php if (!empty($error)): ?>
  <div class="alert-error" style="margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div class="form-wrapper">
<form method="POST" action="<?php echo APP_URL; ?>/productoservicio/actualizar/<?php echo (int)$productoServicio['id']; ?>" id="formEditPS">
    <div class="form-group">
        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required onchange="toggleStock()">
            <option value="">Seleccionar tipo...</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>" 
                        <?php echo ($tipo == $productoServicio['tipo']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tipo); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" 
               value="<?php echo htmlspecialchars($productoServicio['nombre']); ?>"
               placeholder="Ej: Alimento Premium para Perros">
    </div>
    
    <div class="form-group">
        <label for="precio">Precio (S/):</label>
        <input type="number" id="precio" name="precio" required min="0.01" step="0.01" 
               value="<?php echo htmlspecialchars($productoServicio['precio']); ?>"
               placeholder="0.00">
    </div>
    
    <div class="form-group" id="stock-group">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" min="0" 
               value="<?php echo htmlspecialchars($productoServicio['stock']); ?>"
               placeholder="0">
        <small class="help-text">Para servicios, el stock se establecerá automáticamente en 9999</small>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Actualizar Producto/Servicio</button>
        <a href="<?php echo APP_URL; ?>/productoservicio" class="btn-cancelar">Regresar</a>
    </div>
</form>
</div>

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
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    toggleStock();
});
</script>

<style>
.form-wrapper {
    max-width: 470px;
    margin: 45px auto 0;
    background: #20263B;
    padding: 38px 31px 34px 31px;
    border-radius: 12px;
    box-shadow: 0 2px 13px rgba(0,0,0,0.2);
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #fff;
}

input[type="text"], 
input[type="number"], 
select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #353950;
    background: #141828;
    color: #eee;
    font-size: 16px;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
    outline: none;
    border-color: #208cff;
    box-shadow: 0 0 6px rgba(32, 140, 255, 0.33);
}

select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.help-text {
    display: block;
    margin-top: 5px;
    color: #8a95b2;
    font-size: 14px;
    font-style: italic;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    align-items: center;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-guardar {
    background-color: #007bff;
    color: white;
    padding: 13px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 17px;
    font-weight: bold;
    transition: all 0.12s;
    text-decoration: none;
    display: inline-block;
}

.btn-guardar:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-cancelar {
    background-color: #6c757d;
    color: white;
    padding: 13px 24px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.12s;
}

.btn-cancelar:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
    border-radius: 4px;
    padding: 7px 14px;
    margin-bottom: 13px;
    display: inline-block;
}

@media (max-width: 650px) {
    .form-wrapper {
        padding: 16px 6px;
    }
    .form-group {
        margin-bottom: 13px;
    }
    .form-actions {
        flex-direction: column;
    }
    .btn-guardar,
    .btn-cancelar {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
