<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form method="POST" action="<?php echo APP_URL; ?>/usuario/actualizar/<?php echo (int)$usuario['id']; ?>">
    <div class="form-group">
        <label for="nombre">Nombre del Usuario:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" 
               value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
               placeholder="Ej: Dr. Carlos Mendoza">
    </div>
    
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required maxlength="100" 
               value="<?php echo htmlspecialchars($usuario['email']); ?>"
               placeholder="usuario@veterinaria.com">
    </div>
    
    <div class="form-group">
        <label for="password">Nueva Contraseña (opcional):</label>
        <input type="password" id="password" name="password" minlength="6" 
               placeholder="Dejar vacío para mantener la actual">
    </div>
    
    <div class="form-group">
        <label for="rol">Rol:</label>
        <select id="rol" name="rol" required>
            <option value="">Seleccionar rol...</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?php echo htmlspecialchars($rol); ?>" 
                        <?php echo ($rol == $usuario['rol']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rol); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="estado" value="1" 
                   <?php echo $usuario['estado'] ? 'checked' : ''; ?>>
            Usuario activo
        </label>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn-guardar">Actualizar Usuario</button>
        <a href="<?php echo APP_URL; ?>/usuario" class="btn-cancelar">Cancelar</a>
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
    
    .checkbox-label {
        display: flex;
        align-items: center;
        font-weight: normal;
    }
    
    .checkbox-label input[type="checkbox"] {
        margin-right: 8px;
    }
    
    input[type="text"], input[type="email"], input[type="password"], select {
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
