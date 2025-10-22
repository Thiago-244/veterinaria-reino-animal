<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>

<form action="<?php echo APP_URL; ?>/cita/actualizar/<?php echo (int)$cita['id']; ?>" method="POST">
    <div class="form-group">
        <label for="codigo">Código:</label>
        <input type="text" id="codigo" value="<?php echo htmlspecialchars($cita['codigo']); ?>" readonly>
    </div>
    
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required onchange="cargarMascotas()">
            <option value="">Seleccionar Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo (int)$cliente['id']; ?>" 
                        <?php echo ($cliente['id'] == $cita['id_cliente']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="id_mascota">Mascota:</label>
        <select id="id_mascota" name="id_mascota" required>
            <option value="">Seleccionar Mascota</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="fecha_cita">Fecha de la Cita:</label>
        <input type="date" id="fecha_cita" name="fecha_cita" 
               value="<?php echo date('Y-m-d', strtotime($cita['fecha_cita'])); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="hora_cita">Hora de la Cita:</label>
        <input type="time" id="hora_cita" name="hora_cita" 
               value="<?php echo date('H:i', strtotime($cita['fecha_cita'])); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="motivo">Motivo de la Cita:</label>
        <textarea id="motivo" name="motivo" required maxlength="255" rows="4"><?php echo htmlspecialchars($cita['motivo']); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="estado">Estado:</label>
        <select id="estado" name="estado" required>
            <option value="Pendiente" <?php echo ($cita['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
            <option value="Procesada" <?php echo ($cita['estado'] == 'Procesada') ? 'selected' : ''; ?>>Procesada</option>
            <option value="Cancelada" <?php echo ($cita['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
        </select>
    </div>
    
    <button type="submit">Actualizar Cita</button>
</form>

<script>
// Datos de mascotas por cliente
const mascotasPorCliente = <?php echo json_encode($mascotas); ?>;
const citaMascotaId = <?php echo (int)$cita['id_mascota']; ?>;

function cargarMascotas() {
    const clienteSelect = document.getElementById('id_cliente');
    const mascotaSelect = document.getElementById('id_mascota');
    const clienteId = clienteSelect.value;
    
    // Limpiar opciones de mascota
    mascotaSelect.innerHTML = '<option value="">Seleccionar Mascota</option>';
    
    if (clienteId) {
        // Filtrar mascotas por cliente
        const mascotasFiltradas = mascotasPorCliente.filter(mascota => mascota.id_cliente == clienteId);
        
        mascotasFiltradas.forEach(mascota => {
            const option = document.createElement('option');
            option.value = mascota.id;
            option.textContent = mascota.nombre + ' (' + mascota.codigo + ')';
            
            // Seleccionar la mascota actual de la cita
            if (mascota.id == citaMascotaId) {
                option.selected = true;
            }
            
            mascotaSelect.appendChild(option);
        });
    }
}

// Cargar mascotas al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    cargarMascotas();
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
    input[type="time"], 
    select, 
    textarea { 
        width: 100%; 
        padding: 8px; 
        border-radius: 4px; 
        border: 1px solid #ddd; 
        box-sizing: border-box;
        font-family: inherit;
    }
    
    input[readonly] {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    
    textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    button { 
        background-color: #17a2b8; 
        color: white; 
        padding: 12px 20px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 16px;
        font-weight: bold;
    }
    
    button:hover {
        background-color: #138496;
    }
    
    .form-group:last-child {
        margin-top: 25px;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
