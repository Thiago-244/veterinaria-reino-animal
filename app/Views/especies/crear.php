<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form method="POST" action="<?php echo APP_URL; ?>/especie/guardar">
    <div class="form-group">
        <label for="nombre">Nombre de la Especie:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="50" 
               placeholder="Ej: Canino, Felino, Ave, etc.">
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Guardar Especie</button>
        <a href="<?php echo APP_URL; ?>/especie" class="btn-cancelar">Cancelar</a>
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
    
    input[type="text"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        max-width: 400px;
    }
    
    input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
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
