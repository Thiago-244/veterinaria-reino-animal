<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form method="POST" action="<?php echo APP_URL; ?>/raza/actualizar/<?php echo (int)$raza['id']; ?>">
    <div class="form-group">
        <label for="id_especie">Especie:</label>
        <select id="id_especie" name="id_especie" required>
            <option value="">Seleccionar especie...</option>
            <?php foreach ($especies as $especie): ?>
                <option value="<?php echo (int)$especie['id']; ?>" 
                        <?php echo ($especie['id'] == $raza['id_especie']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($especie['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="nombre">Nombre de la Raza:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="50" 
               value="<?php echo htmlspecialchars($raza['nombre']); ?>"
               placeholder="Ej: Labrador, Persa, Golden Retriever, etc.">
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Actualizar Raza</button>
        <a href="<?php echo APP_URL; ?>/raza" class="btn-cancelar">Cancelar</a>
    </div>
</form>

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
    
    select, input[type="text"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        max-width: 400px;
    }
    
    select:focus, input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }
    
    .form-actions {
        margin-top: 30px;
    }
    
    .btn-guardar {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-right: 10px;
    }
    
    .btn-guardar:hover {
        background-color: #0056b3;
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
