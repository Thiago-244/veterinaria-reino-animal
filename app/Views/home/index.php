<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Inicio - Reino Animal</title>
</head>
<body>
    <!-- Estas variables vienen desde el HomeController -->
    <h1><?php echo htmlspecialchars($titulo); ?></h1>
    <p><?php echo htmlspecialchars($descripcion); ?></p>
    <p>Esta página se ha cargado a través de nuestro sistema MVC.</p>
</body>
</html>