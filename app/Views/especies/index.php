<?php require APPROOT . '/app/views/layouts/header.php'; ?>

<h1><?php echo htmlspecialchars($titulo); ?></h1>
<a href="<?php echo APP_URL; ?>/especie/crear" class="btn-nuevo">Nueva Especie</a>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Total Razas</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($especies as $especie): ?>
            <tr>
                <td><?php echo htmlspecialchars($especie['id']); ?></td>
                <td><?php echo htmlspecialchars($especie['especie_nombre']); ?></td>
                <td>
                    <span class="badge-razas"><?php echo htmlspecialchars($especie['total_razas']); ?></span>
                </td>
                <td>
                    <a href="<?php echo APP_URL; ?>/especie/editar/<?php echo (int)$especie['id']; ?>">Editar</a>
                    |
                    <a href="<?php echo APP_URL; ?>/especie/eliminar/<?php echo (int)$especie['id']; ?>" 
                       onclick="return confirm('¿Eliminar especie?');">Eliminar</a>
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
    
    .badge-razas {
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
