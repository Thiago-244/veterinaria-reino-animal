<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo ?? 'Reino Animal'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/app.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar__brand">
                <a href="<?php echo APP_URL; ?>/dashboard" class="brand">
                    <span class="brand__logo">🩺</span>
                    <span class="brand__name">Reino Animal</span>
                </a>
                <button class="sidebar__close" id="sidebarClose" aria-label="Cerrar menú">✕</button>
            </div>
            <nav class="sidebar__nav">
                <?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } $rol = $_SESSION['usuario_rol'] ?? null; ?>
                <a class="nav__item" href="<?php echo APP_URL; ?>/dashboard">🏠 Dashboard</a>
                <div class="nav__section">Gestión</div>
                <a class="nav__item" href="<?php echo APP_URL; ?>/cliente">👥 Clientes</a>
                <a class="nav__item" href="<?php echo APP_URL; ?>/mascota">🐾 Mascotas</a>
                <a class="nav__item" href="<?php echo APP_URL; ?>/cita">📅 Citas</a>
                <a class="nav__item" href="<?php echo APP_URL; ?>/productoservicio">📦 Productos/Servicios</a>
                <?php if ($rol !== 'Consultor') { ?>
                    <a class="nav__item" href="<?php echo APP_URL; ?>/venta">💰 Ventas</a>
                <?php } ?>
                <div class="nav__section">Catálogos</div>
                <a class="nav__item" href="<?php echo APP_URL; ?>/especie">🧬 Especies</a>
                <a class="nav__item" href="<?php echo APP_URL; ?>/raza">🐕 Razas</a>
                <?php if ($rol === 'Administrador') { ?>
                    <div class="nav__section">Sistema</div>
                    <a class="nav__item" href="<?php echo APP_URL; ?>/usuario">👤 Usuarios</a>
                <?php } ?>
                <a class="nav__item" href="<?php echo APP_URL; ?>/login/logout">🚪 Cerrar sesión</a>
            </nav>
            <div class="sidebar__footer">
                <small>© <?php echo date('Y'); ?> Reino Animal</small>
            </div>
        </aside>

        <div class="content">
            <header class="topbar">
                <button class="topbar__menu" id="sidebarOpen" aria-label="Abrir menú">☰</button>
                <h1 class="topbar__title"><?php echo htmlspecialchars($titulo ?? ''); ?></h1>
                <div class="topbar__actions">
                    <button class="btn btn--ghost" id="themeToggle" title="Cambiar tema">🌓</button>
                </div>
            </header>

            <main class="page">