<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form action="<?php echo APP_URL; ?>/cliente/actualizar/<?php echo (int)$cliente['id']; ?>" method="POST">
    <div class="form-group">
        <label for="dni">DNI:</label>
        <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($cliente['dni']); ?>" required>
    </div>
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
    </div>
    <div class="form-group">
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
    </div>
    <div class="form-group">
        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>">
    </div>
    <button type="submit">Actualizar Cliente</button>
</form>

<style>
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; }
    input[type="text"], input[type="email"] { width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd; }
    button { background-color: #17a2b8; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>


