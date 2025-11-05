<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<?php 
$showMsg = false; 
$msg = '';
if (isset($_GET['success']) && $_GET['success']=='1') { $showMsg=true; $msg='Mascota creada correctamente.'; }
if (isset($_GET['success']) && $_GET['success']=='2') { $showMsg=true; $msg='Mascota actualizada correctamente.'; }
if (isset($_GET['success']) && $_GET['success']=='3') { $showMsg=true; $msg='Mascota eliminada correctamente.'; }
?>
<?php if ($showMsg): ?>
    <div class="alert-success" style="margin-bottom:18px; text-align:center; max-width:500px;margin-left:auto;margin-right:auto;">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>
<div class="row-bar" style="display:flex; justify-content:space-between;align-items:center;margin-bottom:18px">
  <form method="GET" action="" style="display:flex;align-items:center;gap:10px;">
    <input type="text" name="buscar" value="<?php echo isset($buscar)?htmlspecialchars($buscar):''; ?>" placeholder="Buscar por nombre, cliente o código..." style="padding:9px 14px;font-size:15px; border-radius:6px; border:1.3px solid #28304A;min-width:245px;background:#151b2b;color:#fff;margin-right:3px;">
    <button type="submit" class="btn--buscar">Buscar</button>
    <?php if (!empty($buscar)) : ?>
    <a href="<?php echo APP_URL; ?>/mascota" style="font-size:14px;margin-left:8px;color:#bd2130;background:none;border:none;text-decoration:underline;">Limpiar</a>
    <?php endif; ?>
  </form>
  <a href="<?php echo APP_URL; ?>/mascota/crear" class="btn btn--success mb-3" style="font-size:15.5px; padding:12px 22px;">Añadir Nueva Mascota</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Cliente</th>
            <th>Especie/Raza</th>
            <th>Sexo</th>
            <th>Edad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($mascotas as $mascota): ?>
            <tr>
                <td><?php echo htmlspecialchars($mascota['codigo']); ?></td>
                <td><?php echo htmlspecialchars($mascota['nombre']); ?></td>
                <td><?php echo htmlspecialchars($mascota['cliente_nombre'] . ' ' . $mascota['cliente_apellido']); ?></td>
                <td><span class="badge badge--info"><?php echo htmlspecialchars($mascota['especie_nombre'] . ' - ' . $mascota['raza_nombre']); ?></span></td>
                <td><span class="badge badge--dark"><?php echo htmlspecialchars($mascota['sexo']); ?></span></td>
                <td>
                    <?php 
                    if ($mascota['fecha_nacimiento']) {
                        $nacimiento = new DateTime($mascota['fecha_nacimiento']);
                        $hoy = new DateTime();
                        $edad = $hoy->diff($nacimiento);
                        echo '<span class="badge badge--success">' . $edad->y . ' años</span>';
                    } else {
                        echo '<span class="badge badge--warn">N/A</span>';
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/mascota/editar/<?php echo (int)$mascota['id']; ?>" class="badge badge--info">Editar</a>
                    <a href="<?php echo APP_URL; ?>/mascota/eliminar/<?php echo (int)$mascota['id']; ?>" class="badge badge--danger" onclick="return confirm('¿Eliminar mascota?');">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>

<style>
.btn--success { background: #28a745; color: #fff !important; border-radius: 7px; padding:11px 21px; text-decoration:none; transition:all .14s; font-weight:600; display:inline-block}
.btn--success:hover { background: #218838;color:#fff; }
.alert-success { background: #d1e7dd; color:#0f5132; border:1px solid #badbcc; border-radius:4px; padding:9px 20px; margin-bottom:13px; font-size:16px;}
.btn--buscar { background:#2072ff; color:#fff; border:none; border-radius:6px; font-size:15.5px;font-weight:600;padding:9px 18px; cursor:pointer; transition:.13s}
.btn--buscar:hover { background:#174b97;}
</style>
