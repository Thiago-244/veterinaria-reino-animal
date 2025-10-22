<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form action="<?php echo APP_URL; ?>/mascota/actualizar/<?php echo (int)$mascota['id']; ?>" method="POST">
    <div class="form-group">
        <label for="codigo">Código:</label>
        <input type="text" id="codigo" value="<?php echo htmlspecialchars($mascota['codigo']); ?>" readonly>
    </div>
    
    <div class="form-group">
        <label for="nombre">Nombre de la Mascota:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($mascota['nombre']); ?>" required maxlength="50">
    </div>
    
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required>
            <option value="">Seleccionar Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo (int)$cliente['id']; ?>" 
                        <?php echo ($cliente['id'] == $mascota['id_cliente']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="id_especie">Especie:</label>
        <select id="id_especie" name="id_especie" required onchange="cargarRazas()">
            <option value="">Seleccionar Especie</option>
            <?php 
            $especiesAgrupadas = [];
            foreach ($especies as $especie) {
                $especiesAgrupadas[$especie['id']] = $especie['nombre'];
            }
            foreach ($especiesAgrupadas as $id => $nombre): ?>
                <option value="<?php echo (int)$id; ?>" 
                        <?php echo ($id == $mascota['especie_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="id_raza">Raza:</label>
        <select id="id_raza" name="id_raza" required>
            <option value="">Seleccionar Raza</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
               value="<?php echo htmlspecialchars($mascota['fecha_nacimiento']); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo" required>
            <option value="">Seleccionar Sexo</option>
            <option value="Macho" <?php echo ($mascota['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
            <option value="Hembra" <?php echo ($mascota['sexo'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="color">Color:</label>
        <input type="text" id="color" name="color" 
               value="<?php echo htmlspecialchars($mascota['color'] ?? ''); ?>" maxlength="50">
    </div>
    
    <div class="form-group">
        <label for="peso">Peso (kg):</label>
        <input type="number" id="peso" name="peso" 
               value="<?php echo htmlspecialchars($mascota['peso'] ?? ''); ?>" 
               step="0.1" min="0" max="200">
    </div>
    
    <button type="submit">Actualizar Mascota</button>
</form>

<script>
// Datos de razas por especie
const razasPorEspecie = <?php echo json_encode($razas); ?>;
const mascotaRazaId = <?php echo (int)$mascota['id_raza']; ?>;

function cargarRazas() {
    const especieSelect = document.getElementById('id_especie');
    const razaSelect = document.getElementById('id_raza');
    const especieId = especieSelect.value;
    
    // Limpiar opciones de raza
    razaSelect.innerHTML = '<option value="">Seleccionar Raza</option>';
    
    if (especieId) {
        // Filtrar razas por especie
        const razasFiltradas = razasPorEspecie.filter(raza => raza.id_especie == especieId);
        
        razasFiltradas.forEach(raza => {
            const option = document.createElement('option');
            option.value = raza.id;
            option.textContent = raza.nombre;
            
            // Seleccionar la raza actual de la mascota
            if (raza.id == mascotaRazaId) {
                option.selected = true;
            }
            
            razaSelect.appendChild(option);
        });
    }
}

// Cargar razas al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarRazas();
});
</script>

<style>
    .form-group { 
        margin-bottom: 15px; 
    }
    
    label { 
        display: block; 
        margin-bottom: 5px; 
        font-weight: bold;
    }
    
    input[type="text"], 
    input[type="date"], 
    input[type="number"], 
    select { 
        width: 100%; 
        padding: 8px; 
        border-radius: 4px; 
        border: 1px solid #ddd; 
        box-sizing: border-box;
    }
    
    input[readonly] {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    
    button { 
        background-color: #17a2b8; 
        color: white; 
        padding: 10px 15px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 16px;
    }
    
    button:hover {
        background-color: #138496;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
