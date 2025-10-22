<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1>Nueva Cita Médica</h1>

<form action="<?php echo APP_URL; ?>/cita/guardar" method="POST">
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente" required onchange="cargarMascotas()">
            <option value="">Seleccionar Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo (int)$cliente['id']; ?>">
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="id_mascota">Mascota:</label>
        <select id="id_mascota" name="id_mascota" required>
            <option value="">Primero seleccione un cliente</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="fecha_cita">Fecha de la Cita:</label>
        <input type="date" id="fecha_cita" name="fecha_cita" required min="<?php echo date('Y-m-d'); ?>">
    </div>
    
    <div class="form-group">
        <label for="hora_cita">Hora de la Cita:</label>
        <input type="time" id="hora_cita" name="hora_cita" required>
    </div>
    
    <div class="form-group">
        <label for="motivo">Motivo de la Cita:</label>
        <textarea id="motivo" name="motivo" required maxlength="255" rows="4" placeholder="Describa el motivo de la cita..."></textarea>
    </div>
    
    <button type="submit">Programar Cita</button>
</form>

<script>
// Datos de mascotas por cliente
const mascotasPorCliente = <?php echo json_encode($mascotas); ?>;

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
            mascotaSelect.appendChild(option);
        });
    }
}

// Establecer hora por defecto (próxima hora disponible)
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const nextHour = new Date(now.getTime() + 60 * 60 * 1000);
    const timeInput = document.getElementById('hora_cita');
    timeInput.value = nextHour.toTimeString().slice(0, 5);
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
    
    textarea {
        resize: vertical;
        min-height: 80px;
    }
    
    button { 
        background-color: #28a745; 
        color: white; 
        padding: 12px 20px; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 16px;
        font-weight: bold;
    }
    
    button:hover {
        background-color: #218838;
    }
    
    .form-group:last-child {
        margin-top: 25px;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
