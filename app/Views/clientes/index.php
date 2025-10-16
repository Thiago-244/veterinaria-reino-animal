<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo); ?></title>
    </head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($titulo); ?></h1>
        
        <table border="1" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 8px;">DNI</th>
                    <th style="padding: 8px;">Nombre</th>
                    <th style="padding: 8px;">Apellido</th>
                    <th style="padding: 8px;">Teléfono</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($cliente['dni']); ?></td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($cliente['apellido']); ?></td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($cliente['telefono']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>