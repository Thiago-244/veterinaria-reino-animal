<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Editar Cita Médica</h1>
<?php if (!empty($error)): ?>
  <div class="alert-error" style="margin-bottom: 16px; text-align:center;"> <?php echo htmlspecialchars($error); ?> </div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formCitaEdit" action="<?php echo APP_URL; ?>/apicita/actualizar/<?php echo (int)$cita['id']; ?>" method="POST" autocomplete="off">
    <div class="form-group">
        <label for="id_cliente">Cliente:</label>
        <select id="id_cliente" name="id_cliente">
            <option value="">Seleccionar Cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo (int)$cliente['id']; ?>" <?php echo ($cliente['id'] == $cita['id_cliente']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="id_mascota">Mascota:</label>
        <select id="id_mascota" name="id_mascota">
            <option value="">Seleccionar Mascota</option>
        </select>
    </div>
    <div class="form-group">
        <label for="fecha_cita">Fecha de la Cita:</label>
        <input type="date" id="fecha_cita" name="fecha_cita" value="<?php echo date('Y-m-d', strtotime($cita['fecha_cita'])); ?>">
    </div>
    <div class="form-group">
        <label for="hora_cita">Hora de la Cita:</label>
        <input type="time" id="hora_cita" name="hora_cita" value="<?php echo date('H:i', strtotime($cita['fecha_cita'])); ?>">
    </div>
    <div class="form-group">
        <label for="motivo">Motivo de la Cita:</label>
        <textarea id="motivo" name="motivo" maxlength="255" rows="4"><?php echo htmlspecialchars($cita['motivo']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="estado">Estado:</label>
        <select id="estado" name="estado">
            <option value="Pendiente" <?php echo ($cita['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
            <option value="Procesada" <?php echo ($cita['estado'] == 'Procesada') ? 'selected' : ''; ?>>Procesada</option>
            <option value="Cancelada" <?php echo ($cita['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
        </select>
    </div>
    <div style="display:flex;justify-content:center;gap:14px;margin-top:20px;">
      <button type="submit" style="min-width:200px;">Actualizar Cita</button>
      <a href="<?php echo APP_URL; ?>/cita" style="min-width: 150px; text-align:center; display:flex;align-items:center;justify-content:center; background:#353950; color:#eee; border-radius:6px; padding:13px 0; text-decoration:none; font-size:17px; font-weight:bold; border:none;">Regresar</a>
    </div>
</form>
</div>
<script>
const apiMascotaUrl = "<?php echo APP_URL; ?>/apimascota/por-cliente/";
const citaMascotaId = <?php echo (int)$cita['id_mascota']; ?>;
function cargarMascotasAjaxEdit(clienteId, callback) {
    const mascotaSelect = document.getElementById('id_mascota');
    mascotaSelect.innerHTML = '<option value="">Cargando mascotas...</option>';
    if (!clienteId) {
        mascotaSelect.innerHTML = '<option value="">Primero seleccione un cliente</option>';
        return;
    }
    fetch(apiMascotaUrl + clienteId)
        .then(r => r.json())
        .then(data => {
            mascotaSelect.innerHTML = '';
            if (data.data && data.data.length) {
                mascotaSelect.innerHTML = '<option value="">Seleccionar Mascota</option>';
                data.data.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.textContent = m.nombre + ' (' + m.codigo + ')';
                    if (m.id == citaMascotaId) opt.selected = true;
                    mascotaSelect.appendChild(opt);
                });
            } else {
                mascotaSelect.innerHTML = '<option value="">No tiene mascotas registradas</option>';
            }
            if (callback) callback();
        })
        .catch(() => { mascotaSelect.innerHTML = '<option value="">Error al cargar mascotas</option>'; });
}
document.getElementById('id_cliente').addEventListener('change', function () {
    cargarMascotasAjaxEdit(this.value);
});
window.addEventListener('DOMContentLoaded', ()=>{
    cargarMascotasAjaxEdit(document.getElementById('id_cliente').value);
});
const form = document.getElementById('formCitaEdit');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(e=>e.textContent='');
    document.getElementById('alertResult').textContent = '';
    const fd = new FormData(form);
    let fecha = fd.get('fecha_cita');
    let hora = fd.get('hora_cita');
    let cita = {
      id_cliente: fd.get('id_cliente'),
      id_mascota: fd.get('id_mascota'),
      fecha_cita: (fecha && hora) ? (fecha + ' ' + hora + ':00') : '',
      motivo: fd.get('motivo'),
      estado: fd.get('estado')
    };
    fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(cita)
    }).then(r => r.json().then(data => ({status: r.status, body: data})))
      .then(res => {
        if (res.body.error) {
            let mensaje = res.body.error;
            let foundField = '';
            ['cliente','mascota','fecha','motivo','estado'].forEach(f=>{
                if(mensaje.toLowerCase().includes(f)) foundField = f;
            });
            if (foundField && document.getElementById('err_'+(foundField==='cliente'?'id_cliente':foundField==='mascota'?'id_mascota':foundField)) ) {
                document.getElementById('err_'+(foundField==='cliente'?'id_cliente':foundField==='mascota'?'id_mascota':foundField)).textContent = mensaje;
            } else {
                document.getElementById('alertResult').innerHTML = '<div class="alert-error">'+mensaje+'</div>';
            }
        } else if(res.body.message) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-success">'+res.body.message+'</div>';
        } else {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">Error desconocido</div>';
        }
      })
      .catch(()=>{
        document.getElementById('alertResult').innerHTML = '<div class="alert-error">No se pudo conectar al servidor</div>';
      });
});
</script>
<style>
.form-wrapper{max-width:520px;margin:20px auto;background:#20263B;padding:28px;border-radius:10px;box-shadow:0 2px 13px #0002;}
.form-group { margin-bottom: 15px; }
label { display:block; margin-bottom:5px; font-weight:bold; }
select, input[type=date], input[type=time], textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #353950; background:#141828; color:#eee; }
.alert-error { background: #f8d7da; color: #842029; border:1px solid #f5c2c7; border-radius:4px; padding:7px 14px; }
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:7px 14px; }
button { background-color:#17a2b8; color:white; padding:12px 20px; border:none; border-radius:5px; font-size:16px; font-weight: bold; cursor:pointer; }
button:hover { background-color:#138496; }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
