<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Editar Especie</h1>
<?php if (!empty($error)): ?>
  <div class="alert-error" style="margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formEspecieEdit" action="<?php echo APP_URL; ?>/apiespecie/actualizar/<?php echo (int)$especie['id']; ?>" method="POST" autocomplete="off">
    <div class="form-group">
        <label for="nombre">Nombre de la Especie: <span class="required">*</span></label>
        <input type="text" id="nombre" name="nombre" required maxlength="50" 
               value="<?php echo htmlspecialchars($especie['nombre']); ?>"
               placeholder="Ej: Canino, Felino, Ave, etc.">
        <div class="field-error" id="err_nombre"></div>
        <small class="help-text">Mínimo 2 caracteres, máximo 50 caracteres</small>
    </div>
    
    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
        <button type="submit" style="min-width: 200px;">✏️ Actualizar Especie</button>
        <a href="<?php echo APP_URL; ?>/especie" class="btn-cancelar">🔙 Regresar</a>
    </div>
</form>
</div>

<script>
const form = document.getElementById('formEspecieEdit');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('alertResult').textContent = '';
    document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    
    const fd = new FormData(form);
    const especie = {
        nombre: fd.get('nombre').trim()
    };
    
    // Validación frontend
    if (!especie.nombre || especie.nombre.length < 2) {
        document.getElementById('err_nombre').textContent = 'El nombre debe tener al menos 2 caracteres';
        return;
    }
    if (especie.nombre.length > 50) {
        document.getElementById('err_nombre').textContent = 'El nombre no debe superar 50 caracteres';
        return;
    }
    
    fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(especie)
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(res => {
        if (res.body.error) {
            let mensaje = res.body.error;
            if (mensaje.toLowerCase().includes('nombre')) {
                document.getElementById('err_nombre').textContent = mensaje;
            } else {
                document.getElementById('alertResult').innerHTML = '<div class="alert-error">' + mensaje + '</div>';
            }
        } else if (res.body.message) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-success">' + res.body.message + '</div>';
            setTimeout(() => {
                window.location = '<?php echo APP_URL; ?>/especie?success=2';
            }, 1500);
        } else {
            document.getElementById('alertResult').innerHTML = '<div class="alert-error">Error desconocido</div>';
        }
    })
    .catch(() => {
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

.required {
    color: #ff6b6b;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #353950;
    background: #141828;
    color: #eee;
    font-size: 16px;
    box-sizing: border-box;
}

input[type="text"]:focus {
    outline: none;
    border-color: #208cff;
    box-shadow: 0 0 6px rgba(32, 140, 255, 0.33);
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
    background-color: #17a2b8;
    color: white;
    padding: 13px 0;
    border: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
    width: 200px;
    transition: all 0.12s;
}

button:hover {
    background-color: #138496;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
}

.btn-cancelar {
    background-color: #6c757d;
    color: white;
    padding: 13px 24px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 17px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.12s;
    min-width: 150px;
    text-align: center;
}

.btn-cancelar:hover {
    background-color: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

@media (max-width: 650px) {
    .form-wrapper {
        padding: 16px 6px;
    }
    .form-group {
        margin-bottom: 13px;
    }
    button, .btn-cancelar {
        width: 100%;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
