<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1>Añadir Nueva Mascota</h1>

<form action="<?php echo APP_URL; ?>/mascota/guardar" method="POST">
    <div class="form-group">
        <label for="nombre">Nombre de la Mascota:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="50">
    </div>
    
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required>
            <option value="">Seleccionar Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo (int)$cliente['id']; ?>">
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
                <option value="<?php echo (int)$id; ?>">
                    <?php echo htmlspecialchars($nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="id_raza">Raza:</label>
        <select id="id_raza" name="id_raza" required>
            <option value="">Primero seleccione una especie</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
    </div>
    
    <div class="form-group">
        <label for="sexo">Sexo:</label>
        <select id="sexo" name="sexo" required>
            <option value="">Seleccionar Sexo</option>
            <option value="Macho">Macho</option>
            <option value="Hembra">Hembra</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="color">Color:</label>
        <input type="text" id="color" name="color" maxlength="50">
    </div>
    
    <div class="form-group">
        <label for="peso">Peso (kg):</label>
        <input type="number" id="peso" name="peso" step="0.1" min="0" max="200">
    </div>
    
    <button type="submit">Guardar Mascota</button>
</form>

<script>
// Datos de razas por especie
const razasPorEspecie = <?php echo json_encode($razas); ?>;

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
            razaSelect.appendChild(option);
        });
    }
}
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
    
    button { 
        background-color: #007bff; 
        color: white; 
        padding: 10px 15px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 16px;
    }
    
    button:hover {
        background-color: #0056b3;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
