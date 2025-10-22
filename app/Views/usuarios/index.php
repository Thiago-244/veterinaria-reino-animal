<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/usuario/crear" class="btn-nuevo">Nuevo Usuario</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
            <tr class="<?php echo $usuario['estado'] ? 'usuario-activo' : 'usuario-inactivo'; ?>">
                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td>
                    <span class="badge-rol badge-<?php echo strtolower($usuario['rol']); ?>">
                        <?php echo htmlspecialchars($usuario['rol']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge-estado <?php echo $usuario['estado'] ? 'activo' : 'inactivo'; ?>">
                        <?php echo $usuario['estado'] ? 'Activo' : 'Inactivo'; ?>
                    </span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/usuario/editar/<?php echo (int)$usuario['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/usuario/eliminar/<?php echo (int)$usuario['id']; ?>" 
                       onclick="return confirm('¿Eliminar usuario?');">Eliminar</a>
                    |
                    <form method="POST" action="<?php echo APP_URL; ?>/usuario/cambiar-estado/<?php echo (int)$usuario['id']; ?>" style="display: inline;">
                        <button type="submit" class="btn-estado <?php echo $usuario['estado'] ? 'desactivar' : 'activar'; ?>">
                            <?php echo $usuario['estado'] ? 'Desactivar' : 'Activar'; ?>
                        </button>
                        <input type="hidden" name="estado" value="<?php echo $usuario['estado'] ? 0 : 1; ?>">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<style>
    .btn-nuevo {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 15px;
        background-color: #28a745;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .usuario-activo {
        background-color: #d4edda;
    }
    
    .usuario-inactivo {
        background-color: #f8d7da;
    }
    
    .badge-rol {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .badge-administrador {
        background-color: #dc3545;
        color: white;
    }
    
    .badge-editor {
        background-color: #ffc107;
        color: #000;
    }
    
    .badge-consultor {
        background-color: #17a2b8;
        color: white;
    }
    
    .badge-estado {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .badge-estado.activo {
        background-color: #28a745;
        color: white;
    }
    
    .badge-estado.inactivo {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-estado {
        padding: 4px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: bold;
    }
    
    .btn-estado.activar {
        background-color: #28a745;
        color: white;
    }
    
    .btn-estado.desactivar {
        background-color: #dc3545;
        color: white;
    }
    
    a {
        color: #007bff;
        text-decoration: none;
    }
    
    a:hover {
        text-decoration: underline;
    }
</style>

<?php require APPROOT . '/app/views/layouts/footer.php'; ?>
