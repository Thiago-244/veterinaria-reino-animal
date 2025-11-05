<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Crear Nuevo Usuario</h1>
<?php if (!empty($error)): ?>
    <div class="alert-error" style="margin-bottom: 16px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<div id="alertResult" style="margin-bottom: 10px; text-align:center"></div>
<div class="form-wrapper">
<form id="formUsuario" action="<?php echo APP_URL; ?>/apiusuario/crear" method="POST" autocomplete="off">
    <div class="form-group">
        <label for="nombre">Nombre del Usuario: <span class="required">*</span></label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" 
               placeholder="Ej: Dr. Carlos Mendoza"
               value="<?php echo isset($usuario['nombre']) ? htmlspecialchars($usuario['nombre']) : ''; ?>">
        <div class="field-error" id="err_nombre"></div>
        <small class="help-text">Máximo 100 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="email">Email: <span class="required">*</span></label>
        <input type="email" id="email" name="email" required maxlength="100" 
               placeholder="usuario@veterinaria.com"
               value="<?php echo isset($usuario['email']) ? htmlspecialchars($usuario['email']) : ''; ?>">
        <div class="field-error" id="err_email"></div>
        <small class="help-text">Formato válido de email, máximo 100 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="password">Contraseña: <span class="required">*</span></label>
        <input type="password" id="password" name="password" required minlength="6" 
               placeholder="Mínimo 6 caracteres">
        <div class="field-error" id="err_password"></div>
        <small class="help-text">Mínimo 6 caracteres</small>
    </div>
    
    <div class="form-group">
        <label for="rol">Rol: <span class="required">*</span></label>
        <select id="rol" name="rol" required>
            <option value="">Seleccionar rol...</option>
            <?php foreach ($roles as $rol): ?>
                <option value="<?php echo htmlspecialchars($rol); ?>" 
                        <?php echo (isset($usuario['rol']) && $usuario['rol'] == $rol) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rol); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="field-error" id="err_rol"></div>
    </div>
    
    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" id="estado" name="estado" value="1" checked>
            <span>Usuario activo</span>
        </label>
        <div class="field-error" id="err_estado"></div>
    </div>
    
    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 20px;">
        <button type="submit" style="min-width: 245px;">💾 Guardar Usuario</button>
        <a href="<?php echo APP_URL; ?>/usuario" class="btn-cancelar">🔙 Regresar</a>
    </div>
</form>
</div>

<script>
const form = document.getElementById('formUsuario');
form.addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelectorAll('.field-error').forEach(el => el.textContent = '');
    document.getElementById('alertResult').textContent = '';
    
    const nombre = document.getElementById('nombre').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const rol = document.getElementById('rol').value;
    const estado = document.getElementById('estado').checked ? 1 : 0;
    
    // Validación frontend básica
    let hasError = false;
    if (!nombre) {
        document.getElementById('err_nombre').textContent = 'El nombre del usuario es requerido';
        hasError = true;
    } else if (nombre.length > 100) {
        document.getElementById('err_nombre').textContent = 'El nombre no debe superar 100 caracteres';
        hasError = true;
    }
    
    if (!email) {
        document.getElementById('err_email').textContent = 'El email es requerido';
        hasError = true;
    } else if (email.length > 100) {
        document.getElementById('err_email').textContent = 'El email no debe superar 100 caracteres';
        hasError = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById('err_email').textContent = 'El email no tiene un formato válido';
        hasError = true;
    }
    
    if (!password) {
        document.getElementById('err_password').textContent = 'La contraseña es requerida';
        hasError = true;
    } else if (password.length < 6) {
        document.getElementById('err_password').textContent = 'La contraseña debe tener al menos 6 caracteres';
        hasError = true;
    }
    
    if (!rol) {
        document.getElementById('err_rol').textContent = 'El rol es requerido';
        hasError = true;
    } else if (!['Administrador', 'Editor', 'Consultor'].includes(rol)) {
        document.getElementById('err_rol').textContent = 'El rol debe ser Administrador, Editor o Consultor';
        hasError = true;
    }
    
    if (hasError) return;
    
    const usuario = {
        nombre,
        email,
        password,
        rol,
        estado
    };
    
    fetch(form.action, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(usuario)
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(res => {
        if (res.body.error) {
            let mensaje = res.body.error;
            let foundField = '';
            ['nombre', 'email', 'password', 'rol', 'estado'].forEach(f => {
                if (mensaje.toLowerCase().includes(f)) {
                    foundField = f;
                }
            });
            if (foundField && document.getElementById('err_' + foundField)) {
                document.getElementById('err_' + foundField).textContent = mensaje;
            } else {
                document.getElementById('alertResult').innerHTML = '<div class="alert-error">' + mensaje + '</div>';
            }
        } else if (res.body.message) {
            document.getElementById('alertResult').innerHTML = '<div class="alert-success">' + res.body.message + '</div>';
            setTimeout(() => {
                window.location = '<?php echo APP_URL; ?>/usuario?success=1';
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
    max-width: 520px;
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

input[type="text"],
input[type="email"],
input[type="password"],
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
input[type="email"]:focus,
input[type="password"]:focus,
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

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: auto;
    cursor: pointer;
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
    min-width: 200px;
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
