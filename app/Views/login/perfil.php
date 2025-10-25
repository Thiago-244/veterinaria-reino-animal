<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<div class="profile-container">
    <div class="profile-box">
        <div class="profile-header">
            <h1>👤 Mi Perfil</h1>
            <p>Información de tu cuenta</p>
        </div>

        <div class="profile-content">
            <div class="profile-info">
                <div class="info-section">
                    <h3>📋 Información Personal</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nombre:</label>
                            <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Rol:</label>
                            <span class="role-badge role-<?php echo strtolower($usuario['rol']); ?>">
                                <?php echo htmlspecialchars($usuario['rol']); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <label>Estado:</label>
                            <span class="status-badge status-<?php echo $usuario['estado'] ? 'active' : 'inactive'; ?>">
                                <?php echo $usuario['estado'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h3>📅 Información de Cuenta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Miembro desde:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Última actualización:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($usuario['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <h3>⚙️ Acciones</h3>
                <div class="action-buttons">
                    <a href="<?php echo APP_URL; ?>/login/cambiar-password" class="btn-action btn-primary">
                        🔐 Cambiar Contraseña
                    </a>
                    <a href="<?php echo APP_URL; ?>/dashboard" class="btn-action btn-secondary">
                        🏠 Ir al Dashboard
                    </a>
                    <a href="<?php echo APP_URL; ?>/login/logout" class="btn-action btn-danger" 
                       onclick="return confirm('¿Estás seguro de cerrar sesión?')">
                        🚪 Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>

        <div class="profile-footer">
            <div class="security-info">
                <h4>🔒 Seguridad de la Cuenta</h4>
                <div class="security-items">
                    <div class="security-item">
                        <span class="security-icon">✅</span>
                        <span>Contraseña segura configurada</span>
                    </div>
                    <div class="security-item">
                        <span class="security-icon">🛡️</span>
                        <span>Sesión activa y protegida</span>
                    </div>
                    <div class="security-item">
                        <span class="security-icon">📧</span>
                        <span>Email verificado</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px;
}

.profile-box {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    overflow: hidden;
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

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.profile-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.profile-header p {
    margin: 0;
    opacity: 0.9;
}

.profile-content {
    padding: 30px;
}

.info-section {
    margin-bottom: 30px;
}

.info-section h3 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 18px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item label {
    font-weight: 600;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item span {
    color: #333;
    font-size: 16px;
}

.role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.role-administrador {
    background: #ff6b6b;
    color: white;
}

.role-editor {
    background: #4ecdc4;
    color: white;
}

.role-consultor {
    background: #45b7d1;
    color: white;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background: #51cf66;
    color: white;
}

.status-inactive {
    background: #ffa8a8;
    color: white;
}

.profile-actions {
    border-top: 1px solid #f0f0f0;
    padding-top: 30px;
}

.profile-actions h3 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 18px;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.btn-action {
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.2s ease;
    border: 2px solid transparent;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f8f9fa;
    color: #666;
    border-color: #e1e5e9;
}

.btn-secondary:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.btn-danger {
    background: #ff6b6b;
    color: white;
}

.btn-danger:hover {
    background: #ff5252;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
}

.profile-footer {
    background: #f8f9fa;
    padding: 30px;
    border-top: 1px solid #e1e5e9;
}

.security-info h4 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 16px;
}

.security-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.security-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #666;
    font-size: 14px;
}

.security-icon {
    font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-content {
        padding: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .profile-container {
        padding: 10px;
    }
    
    .profile-header {
        padding: 20px;
    }
    
    .profile-header h1 {
        font-size: 24px;
    }
}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
