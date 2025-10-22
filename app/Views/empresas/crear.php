<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form method="POST" action="<?php echo APP_URL; ?>/empresa/guardar">
    <div class="form-group">
        <label for="nombre">Nombre de la Empresa:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" 
               placeholder="Ej: Veterinaria Reino Animal">
    </div>
    
    <div class="form-group">
        <label for="ruc">RUC:</label>
        <input type="text" id="ruc" name="ruc" required maxlength="11" pattern="[0-9]{11}"
               placeholder="12345678901">
    </div>
    
    <div class="form-group">
        <label for="direccion">Dirección:</label>
        <textarea id="direccion" name="direccion" required maxlength="255" 
                  placeholder="Dirección completa de la empresa"></textarea>
    </div>
    
    <div class="form-group">
        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required maxlength="15" 
               placeholder="Ej: 01-234-5678">
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required maxlength="100" 
               placeholder="info@empresa.com">
    </div>
    
    <div class="form-group">
        <label for="logo">Logo (URL):</label>
        <input type="url" id="logo" name="logo" maxlength="255" 
               placeholder="https://ejemplo.com/logo.png">
    </div>
    
    <div class="form-group">
        <label for="iva">IVA (%):</label>
        <input type="number" id="iva" name="iva" min="0" max="100" step="0.01" 
               value="18.00" placeholder="18.00">
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Guardar Empresa</button>
        <a href="<?php echo APP_URL; ?>/empresa" class="btn-cancelar">Cancelar</a>
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
    
    input[type="text"], input[type="email"], input[type="url"], input[type="number"], textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        max-width: 500px;
    }
    
    textarea {
        height: 80px;
        resize: vertical;
    }
    
    input:focus, textarea:focus {
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
