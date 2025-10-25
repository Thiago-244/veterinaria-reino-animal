<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1>🔐 Iniciar Sesión</h1>
            <p>Sistema Veterinaria Reino Animal</p>
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

        <form action="<?php echo APP_URL; ?>/login/procesar" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">📧 Email:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="tu@email.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">🔒 Contraseña:</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Tu contraseña"
                >
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1">
                    Recordar sesión
                </label>
            </div>

            <button type="submit" class="btn-login">
                🚀 Iniciar Sesión
            </button>
        </form>

        <div class="login-footer">
            <p>¿Problemas para acceder? Contacta al administrador</p>
        </div>
    </div>
</div>

<style>
.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.login-box {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 400px;
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

.login-header {
    text-align: center;
    margin-bottom: 30px;
}

.login-header h1 {
    color: #333;
    margin: 0 0 10px 0;
    font-size: 28px;
}

.login-header p {
    color: #666;
    margin: 0;
    font-size: 14px;
}

.login-form {
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-group input[type="email"]:focus,
.form-group input[type="password"]:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    color: #666;
}

.checkbox-label input[type="checkbox"] {
    margin-right: 8px;
}

.btn-login {
    width: 100%;
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

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-login:active {
    transform: translateY(0);
}

.login-footer {
    text-align: center;
    margin-top: 20px;
}

.login-footer p {
    color: #666;
    font-size: 12px;
    margin: 0;
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
    .login-box {
        padding: 30px 20px;
        margin: 10px;
    }
    
    .login-header h1 {
        font-size: 24px;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
