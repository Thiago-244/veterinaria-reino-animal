<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<div class="password-container">
    <div class="password-box">
        <div class="password-header">
            <h1>🔐 Cambiar Contraseña</h1>
            <p>Actualiza tu contraseña de acceso</p>
        </div>

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success) && $success): ?>
            <div class="alert alert-success">
                <strong>Éxito:</strong> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo APP_URL; ?>/login/procesar-cambio-password" method="POST" class="password-form">
            <div class="form-group">
                <label for="password_actual">🔒 Contraseña Actual:</label>
                <input 
                    type="password" 
                    id="password_actual" 
                    name="password_actual" 
                    required 
                    placeholder="Ingresa tu contraseña actual"
                >
            </div>

            <div class="form-group">
                <label for="nueva_password">🆕 Nueva Contraseña:</label>
                <input 
                    type="password" 
                    id="nueva_password" 
                    name="nueva_password" 
                    required 
                    placeholder="Mínimo 8 caracteres, letra y número"
                    minlength="8"
                >
                <small class="help-text">
                    Debe tener al menos 8 caracteres, una letra y un número
                </small>
            </div>

            <div class="form-group">
                <label for="confirmar_password">✅ Confirmar Nueva Contraseña:</label>
                <input 
                    type="password" 
                    id="confirmar_password" 
                    name="confirmar_password" 
                    required 
                    placeholder="Repite la nueva contraseña"
                    minlength="8"
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-change">
                    🔄 Cambiar Contraseña
                </button>
                <a href="<?php echo APP_URL; ?>/dashboard" class="btn-cancel">
                    ↩️ Cancelar
                </a>
            </div>
        </form>

        <div class="password-footer">
            <div class="security-tips">
                <h4>💡 Consejos de Seguridad:</h4>
                <ul>
                    <li>Usa una contraseña única</li>
                    <li>Combina letras, números y símbolos</li>
                    <li>No compartas tu contraseña</li>
                    <li>Cambia tu contraseña regularmente</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.password-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.password-box {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 500px;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.password-header {
    text-align: center;
    margin-bottom: 30px;
}

.password-header h1 {
    color: #333;
    margin: 0 0 10px 0;
    font-size: 28px;
}

.password-header p {
    color: #666;
    margin: 0;
    font-size: 14px;
}

.password-form {
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-group input[type="password"]:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.help-text {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-change {
    flex: 1;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.btn-change:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-cancel {
    flex: 1;
    padding: 15px;
    background: #f8f9fa;
    color: #666;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    transition: all 0.2s ease;
    border: 2px solid #e1e5e9;
}

.btn-cancel:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.password-footer {
    border-top: 1px solid #e1e5e9;
    padding-top: 20px;
}

.security-tips h4 {
    color: #333;
    margin: 0 0 15px 0;
    font-size: 16px;
}

.security-tips ul {
    margin: 0;
    padding-left: 20px;
    color: #666;
    font-size: 14px;
}

.security-tips li {
    margin-bottom: 5px;
}

.alert {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-error {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert-success {
    background-color: #efe;
    border: 1px solid #cfc;
    color: #363;
}

/* Responsive */
@media (max-width: 480px) {
    .password-box {
        padding: 30px 20px;
        margin: 10px;
    }
    
    .password-header h1 {
        font-size: 24px;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Validación en tiempo real
document.getElementById('nueva_password').addEventListener('input', function() {
    const password = this.value;
    const confirmPassword = document.getElementById('confirmar_password');
    
    if (confirmPassword.value && password !== confirmPassword.value) {
        confirmPassword.setCustomValidity('Las contraseñas no coinciden');
    } else {
        confirmPassword.setCustomValidity('');
    }
});

document.getElementById('confirmar_password').addEventListener('input', function() {
    const password = document.getElementById('nueva_password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
