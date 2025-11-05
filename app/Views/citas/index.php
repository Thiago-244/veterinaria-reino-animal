<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<?php 
$showMsg = false; 
$msg = '';
if (isset($_GET['success']) && $_GET['success']=='1') { $showMsg=true; $msg='Cita creada correctamente.'; }
if (isset($_GET['success']) && $_GET['success']=='2') { $showMsg=true; $msg='Cita actualizada correctamente.'; }
if (isset($_GET['success']) && $_GET['success']=='3') { $showMsg=true; $msg='Cita eliminada correctamente.'; }
?>
<?php if ($showMsg): ?>
    <div class="alert-success" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>
<h1><?php echo htmlspecialchars($titulo); ?></h1>
<?php 
$showMsg = false; $msg = '';
if (!empty($_SESSION['success_message'])) { $showMsg=true; $msg=$_SESSION['success_message']; unset($_SESSION['success_message']); }
?>
<?php if ($showMsg): ?>
  <div class="alert-success" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;"> <?php echo htmlspecialchars($msg); ?> </div>
<?php endif; ?>
<div class="mb-4 flex-wrap gap-2" style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
  <form method="GET" action="" style="display:flex;align-items:center;gap:10px;">
    <input type="text" name="buscar" value="<?php echo isset($buscar)?htmlspecialchars($buscar):''; ?>" placeholder="Buscar por código, mascota, cliente o motivo..." style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;min-width:300px;background:#151b2b;color:#fff;margin-right:3px;">
    <button type="submit" class="btn--buscar">Buscar</button>
    <?php if (!empty($buscar)) : ?>
      <a href="<?php echo APP_URL; ?>/cita" style="font-size:14px;margin-left:8px;color:#bd2130;background:none;border:none;text-decoration:underline;">Limpiar</a>
    <?php endif; ?>
  </form>
  <div style="display:flex; gap:8px;">
    <a href="<?php echo APP_URL; ?>/cita/crear" class="btn btn--success">Nueva Cita Médica</a>
    <a href="<?php echo APP_URL; ?>/cita/calendario" class="btn btn--info">Ver Calendario</a>
  </div>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Fecha y Hora</th>
            <th>Mascota</th>
            <th>Cliente</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $cita): ?>
            <tr>
                <td><?php echo htmlspecialchars($cita['codigo']); ?></td>
                <td>
                    <?php 
                    $fecha = new DateTime($cita['fecha_cita']);
                    echo $fecha->format('d/m/Y H:i');
                    ?>
                </td>
                <td><?php echo htmlspecialchars($cita['mascota_nombre'] . ' (' . $cita['mascota_codigo'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($cita['cliente_nombre'] . ' ' . $cita['cliente_apellido']); ?></td>
                <td><?php echo htmlspecialchars($cita['motivo']); ?></td>
                <td>
                    <?php
                    $est = strtolower($cita['estado']);
                    $badgeEstado = $est === 'pendiente'   ? 'badge--warn'
                                : ($est === 'procesada'   ? 'badge--success' : 'badge--danger');
                    ?>
                    <span class="badge <?php echo $badgeEstado; ?>"><?php echo htmlspecialchars($cita['estado']); ?></span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/cita/editar/<?php echo (int)$cita['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/cita/eliminar/<?php echo (int)$cita['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar cita?');">Eliminar</a>
                    <form method="POST" action="<?php echo APP_URL; ?>/cita/cambiar-estado/<?php echo (int)$cita['id']; ?>" style="display: inline;">
                        <select name="estado" onchange="this.form.submit()" class="badge" style="min-width: 98px; padding: 4px 9px; border-radius: 7px; font-size: 13.5px; margin-left: 7px;">
                            <option value="Pendiente" <?php echo ($cita['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="Procesada" <?php echo ($cita['estado'] == 'Procesada') ? 'selected' : ''; ?>>Procesada</option>
                            <option value="Cancelada" <?php echo ($cita['estado'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>

<style>
.btn--success { background: #28a745; color:#fff !important; border-radius:7px; padding:10px 18px; text-decoration:none; font-weight:600; }
.btn--success:hover { background:#218838; }
.btn--info { background:#17a2b8; color:#fff !important; border-radius:7px; padding:10px 18px; text-decoration:none; font-weight:600; }
.btn--info:hover { background:#138496; }
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:9px 20px; margin-bottom:13px; font-size:16px;}
.btn--buscar { background:#2072ff; color:#fff; border:none; border-radius:6px; font-size:15.5px;font-weight:600;padding:9px 18px; cursor:pointer; transition:.13s}
.btn--buscar:hover { background:#174b97;}
</style>
