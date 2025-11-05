<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Editar Cliente</h1>
<?php if (!empty($error)): ?>
    <div class="alert-error" style="margin-bottom: 16px; text-align:center;"> <?php echo htmlspecialchars($error); ?> </div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formClienteEdit" action="<?php echo APP_URL; ?>/apicliente/actualizar/<?php echo (int)$cliente['id']; ?>" method="POST" autocomplete="off">
    <div class="form-group"><label for="dni">DNI:</label><input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($cliente['dni']); ?>" required></div>
    <div class="form-group"><label for="nombre">Nombre:</label><input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required></div>
    <div class="form-group"><label for="apellido">Apellido:</label><input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required></div>
    <div class="form-group"><label for="telefono">Teléfono:</label><input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required></div>
    <div class="form-group"><label for="direccion">Dirección:</label><input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion'] ?? ''); ?>"></div>
    <div class="form-group"><label for="email">Email:</label><input type="email" id="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>"></div>
    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
      <button type="submit" style="min-width: 200px;">Actualizar Cliente</button>
      <a href="<?php echo APP_URL; ?>/cliente" style="min-width: 150px; text-align:center; display:flex;align-items:center;justify-content:center; background:#353950; color:#eee; border-radius:6px; padding:13px 0; text-decoration:none; font-size:17px; font-weight:bold; border:none;">Regresar</a>
    </div>
</form>
</div>
<script>
const form = document.getElementById('formClienteEdit');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('alertResult').textContent = '';
    const fd = new FormData(form);
    let cliente = {
        dni: fd.get('dni'), nombre: fd.get('nombre'), apellido: fd.get('apellido'), telefono: fd.get('telefono'), direccion: fd.get('direccion'), email: fd.get('email')
    };
    fetch(form.action, {
        method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(cliente)
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(res => {
        if (res.body.error) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">'+res.body.error+'</div>';
        } else if(res.body.message) {
            window.location = '<?php echo APP_URL; ?>/cliente?success=2';
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
.form-wrapper{max-width:470px;margin:45px auto 0;background:#20263B;padding:38px 31px 34px 31px;border-radius:12px;box-shadow:0 2px 13px #0002;}
.form-group { margin-bottom: 18px; }
label { display:block; margin-bottom:6px; font-weight:600; color:#fff;}
input[type="text"], input[type="email"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #353950; background:#141828; color:#eee;font-size: 16px;} 
input[type="text"]:focus,input[type="email"]:focus{ border-color:#208cff; box-shadow:0 0 6px #208cff55;}
.field-error { color: #d00; font-size: 15px; margin-top:3px; min-height:17px; }
.alert-error { background: #f8d7da; color: #842029; border:1px solid #f5c2c7; border-radius:4px; padding:7px 14px; margin-bottom:13px;}
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:7px 14px; margin-bottom:13px;}
button { background-color:#17a2b8; color:white; padding:13px 0; border:none; border-radius:6px; font-size:17px; font-weight: bold; cursor:pointer; width:200px; transition:all .12s }
button:hover { background-color:#138496;}
a[style*='Regresar'] {transition:all .12s;}
a[style*='Regresar']:hover {background:#232637;color:#fff;}
.form-error-global {display:none;}
@media(max-width: 650px){.form-wrapper{padding:16px 6px}.form-group{margin-bottom:13px}} 
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>


