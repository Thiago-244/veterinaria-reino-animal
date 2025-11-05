<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Añadir Nuevo Producto/Servicio</h1>
<?php if (!empty($error)): ?>
    <div class="alert-error" style="margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formPS" action="<?php echo APP_URL; ?>/apiproductoservicio/crear" method="POST" autocomplete="off">
    <div class="form-group">
        <label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" required onchange="toggleStock()">
            <option value="">Seleccionar tipo...</option>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
            <?php endforeach; ?>
        </select>
        <div class="field-error" id="err_tipo"></div>
    </div>
    
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" placeholder="Ej: Alimento Premium para Perros">
        <div class="field-error" id="err_nombre"></div>
    </div>
    
    <div class="form-group">
        <label for="precio">Precio (S/):</label>
        <input type="number" id="precio" name="precio" required min="0.01" step="0.01" placeholder="0.00">
        <div class="field-error" id="err_precio"></div>
    </div>
    
    <div class="form-group" id="stock-group">
        <label for="stock">Stock:</label>
        <input type="number" id="stock" name="stock" min="0" value="0" placeholder="0">
        <small class="help-text">Para servicios, el stock se establecerá automáticamente en 9999</small>
        <div class="field-error" id="err_stock"></div>
    </div>
    
    <div style="display: flex; justify-content: center; margin-top: 20px;">
        <button type="submit" style="min-width: 245px;">Guardar Producto/Servicio</button>
    </div>
</form>
</div>

<script>
function toggleStock() {
    const tipo = document.getElementById('tipo').value;
    const stockGroup = document.getElementById('stock-group');
    const stockInput = document.getElementById('stock');
    if (tipo === 'Servicio') {
        stockGroup.style.display = 'none';
        stockInput.value = 9999;
    } else {
        stockGroup.style.display = 'block';
        stockInput.value = 0;
    }
}

const form = document.getElementById('formPS');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(e=>e.textContent='');
    document.getElementById('alertResult').textContent = '';
    const fd = new FormData(form);
    let ps = {
        tipo: fd.get('tipo'),
        nombre: fd.get('nombre'),
        precio: fd.get('precio'),
        stock: fd.get('stock'),
    };
    fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(ps)
    }).then(r => r.json().then(data => ({status: r.status, body: data})))
      .then(res => {
        if (res.body.error) {
            let mensaje = res.body.error;
            let foundField = '';
            ['tipo','nombre','precio','stock'].forEach(f=>{
                if(mensaje.toLowerCase().includes(f)) foundField = f;
            });
            if (foundField && document.getElementById('err_'+foundField)) {
                document.getElementById('err_'+foundField).textContent = mensaje;
            } else {
                document.getElementById('alertResult').innerHTML = '<div class="alert-error">'+mensaje+'</div>';
            }
        } else if(res.body.message) {
            form.reset();
            document.getElementById('alertResult').innerHTML = '<div class="alert-success">'+res.body.message+'</div>';
            setTimeout(() => {
                window.location = '<?php echo APP_URL; ?>/productoservicio?success=1';
            }, 1500);
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
.form-wrapper {
    max-width: 470px;
    margin: 45px auto 0;
    background: #20263B;
    padding: 38px 31px 34px 31px;
    border-radius: 12px;
    box-shadow: 0 2px 13px rgba(0,0,0,0.2);
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #fff;
}

input[type="text"], 
input[type="number"], 
select {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #353950;
    background: #141828;
    color: #eee;
    font-size: 16px;
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus {
    outline: none;
    border-color: #208cff;
    box-shadow: 0 0 6px rgba(32, 140, 255, 0.33);
}

select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23fff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 35px;
}

.field-error {
    color: #ff6b6b;
    font-size: 15px;
    margin-top: 3px;
    min-height: 17px;
}

.help-text {
    display: block;
    margin-top: 5px;
    color: #8a95b2;
    font-size: 14px;
    font-style: italic;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
    border-radius: 4px;
    padding: 7px 14px;
    margin-bottom: 13px;
    display: inline-block;
}

.alert-success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
    border-radius: 4px;
    padding: 7px 14px;
    margin-bottom: 13px;
    display: inline-block;
}

button {
    background-color: #007bff;
    color: white;
    padding: 13px 0;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    width: 245px;
    transition: all 0.12s;
}

button:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

@media (max-width: 650px) {
    .form-wrapper {
        padding: 16px 6px;
    }
    .form-group {
        margin-bottom: 13px;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
