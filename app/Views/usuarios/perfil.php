<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1 style="text-align:center">Mi Perfil</h1>
<div class="perfil-wrap">
  <div class="perfil-card">
    <div class="perfil-avatar">
      <span><?php echo strtoupper(substr($usuario['nombre'] ?? 'U',0,1)); ?></span>
    </div>
    <div class="perfil-info">
      <h2><?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></h2>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email'] ?? ''); ?></p>
      <p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol'] ?? ($session['rol'] ?? '')); ?></p>
      <p><strong>Estado:</strong> <?php echo (!empty($usuario['estado']) ? 'Activo' : 'Inactivo'); ?></p>
      <hr>
      <p><strong>Sesión:</strong> <?php echo htmlspecialchars($session['session_id'] ?? ''); ?></p>
      <p><strong>Ingreso:</strong> <?php echo isset($session['login_time']) ? date('d/m/Y H:i', $session['login_time']) : '-'; ?></p>
    </div>
  </div>
  <div class="perfil-actions">
    <a href="<?php echo APP_URL; ?>/usuario/editar/<?php echo (int)($usuario['id'] ?? 0); ?>" class="btn btn--info">Editar mis datos</a>
  </div>
</div>

<style>
.perfil-wrap{max-width:720px;margin:20px auto;padding:10px}
.perfil-card{display:flex;gap:20px;background:#20263B;border-radius:12px;padding:20px;box-shadow:0 2px 13px #0002}
.perfil-avatar{width:90px;height:90px;border-radius:50%;background:#394368;display:flex;align-items:center;justify-content:center;color:#fff;font-size:40px;font-weight:800}
.perfil-info h2{margin:0 0 6px 0}
.perfil-info p{margin:6px 0;color:#dfe6ff}
.perfil-actions{display:flex;justify-content:center;margin-top:16px}
.btn--info{background:#17a2b8;color:#fff !important;border-radius:7px;padding:10px 18px;text-decoration:none;font-weight:600}
.btn--info:hover{background:#138496}
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
