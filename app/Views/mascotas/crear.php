<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Añadir Nueva Mascota</h1>
<?php if (!empty($error)): ?>
  <div class="alert-error" style="margin-bottom: 16px; text-align:center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formMascota" action="<?php echo APP_URL; ?>/apimascota/crear" method="POST" autocomplete="off">
    <div class="form-group"><label for="nombre">Nombre de la Mascota:</label><input type="text" id="nombre" name="nombre"></div>
    <div class="form-group"><label for="id_cliente">Cliente:</label><select id="id_cliente" name="id_cliente"><option value="">Seleccionar Cliente</option><?php foreach ($clientes as $cliente): ?><option value="<?php echo (int)$cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label for="id_especie">Especie:</label><select id="id_especie" name="id_especie" onchange="cargarRazas()"><option value="">Seleccionar Especie</option><?php foreach ($especies as $especie): ?><option value="<?php echo (int)$especie['id']; ?>"><?php echo htmlspecialchars($especie['nombre']); ?></option><?php endforeach; ?></select></div>
    <div class="form-group"><label for="id_raza">Raza:</label><select id="id_raza" name="id_raza"><option value="">Primero seleccione una especie</option></select></div>
    <div class="form-group"><label for="fecha_nacimiento">Fecha de Nacimiento:</label><input type="date" id="fecha_nacimiento" name="fecha_nacimiento"></div>
    <div class="form-group"><label for="sexo">Sexo:</label><select id="sexo" name="sexo"><option value="">Seleccionar Sexo</option><option value="Macho">Macho</option><option value="Hembra">Hembra</option></select></div>
    <div class="form-group"><label for="color">Color:</label><input type="text" id="color" name="color"></div>
    <div class="form-group"><label for="peso">Peso (kg):</label><input type="number" id="peso" name="peso" step="0.1" min="0" max="200"></div>
    <div style="display: flex; justify-content: center; margin-top: 20px;">
      <button type="submit" style="min-width: 200px;">Guardar Mascota</button>
    </div>
</form>
</div>
<script>
// RAZAS por especie, este código ya lo tenías
const razasPorEspecie = <?php echo json_encode($razas); ?>;
function cargarRazas() {
    const especieSelect = document.getElementById('id_especie');
    const razaSelect = document.getElementById('id_raza');
    const especieId = especieSelect.value;
    razaSelect.innerHTML = '<option value="">Seleccionar Raza</option>';
    if (especieId) {
        const razasFiltradas = razasPorEspecie.filter(raza => raza.id_especie == especieId);
        razasFiltradas.forEach(raza => {
            const option = document.createElement('option');
            option.value = raza.id;
            option.textContent = raza.nombre;
            razaSelect.appendChild(option);
        });
    }
}
// Envío de formulario por AJAX + errores
const form = document.getElementById('formMascota');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('alertResult').textContent = '';
    const fd = new FormData(form);
    let mascota = {
      nombre: fd.get('nombre'),
      id_cliente: fd.get('id_cliente'),
      id_raza: fd.get('id_raza'),
      fecha_nacimiento: fd.get('fecha_nacimiento'),
      sexo: fd.get('sexo'),
      color: fd.get('color'),
      peso: fd.get('peso'),
    };
    fetch(form.action, { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(mascota) })
    .then(r => r.json().then(data => ({status:r.status, body:data})))
    .then(res => {
        if (res.body.error) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">'+res.body.error+'</div>';
        } else if(res.body.message) {
            window.location = '<?php echo APP_URL; ?>/mascota?success=1';
        } else {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">Error desconocido</div>';
        }
    })
    .catch(()=>{ document.getElementById('alertResult').innerHTML = '<div class="alert-error">No se pudo conectar al servidor</div>'; });
});
</script>
<style>
.form-group { margin-bottom: 15px; }
label { display:block; margin-bottom:5px; font-weight:bold; }
.field-error { color: #d00; font-size: 14px; margin-top:2px; min-height:18px; }
.alert-error { background: #f8d7da; color: #842029; border:1px solid #f5c2c7; border-radius:4px; padding:7px 14px; }
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:7px 14px; }
button { background-color:#007bff; color:white; padding:12px 20px; border:none; border-radius:5px; font-size:16px; font-weight: bold; cursor:pointer; }
button:hover { background-color:#0056b3; }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
