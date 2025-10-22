<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/raza/crear" class="btn-nuevo">Nueva Raza</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Especie</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($razas as $raza): ?>
            <tr>
                <td><?php echo htmlspecialchars($raza['id']); ?></td>
                <td><?php echo htmlspecialchars($raza['nombre']); ?></td>
                <td>
                    <span class="badge-especie"><?php echo htmlspecialchars($raza['especie_nombre']); ?></span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/raza/editar/<?php echo (int)$raza['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/raza/eliminar/<?php echo (int)$raza['id']; ?>" 
                       onclick="return confirm('¿Eliminar raza?');">Eliminar</a>
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
    
    .badge-especie {
        background-color: #17a2b8;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
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
