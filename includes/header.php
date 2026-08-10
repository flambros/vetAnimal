<?php
// Espera opcionalmente $active = 'inicio'|'turnos'|'diagnosticos'|'tienda'
// Este parcial se incluye siempre desde archivos ubicados en la raíz del sitio.
$usuario = usuario_actual();
?>
<header class="site-header">
    <a href="index.php" class="logo">Veterinaria VetAnimal</a>
    <nav class="main-nav">
        <a href="index.php" class="<?= ($active ?? '') === 'inicio' ? 'active' : '' ?>">Inicio</a>
        <a href="booking.php" class="<?= ($active ?? '') === 'turnos' ? 'active' : '' ?>">Turnos</a>
        <a href="historial.php" class="<?= ($active ?? '') === 'diagnosticos' ? 'active' : '' ?>">Diagnósticos</a>
        <a href="tienda.php" class="<?= ($active ?? '') === 'tienda' ? 'active' : '' ?>">Tienda</a>
    </nav>
    <div class="header-actions">
        <?php if ($usuario): ?>
            <?php if ($usuario['rol'] === 'veterinario'): ?>
                <a href="admin/dashboard.php" class="btn btn-outline btn-sm">Panel Veterinario</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-light btn-sm">Salir</a>
            <a href="perfil.php" class="avatar-btn"><?= strtoupper(substr($usuario['nombre'], 0, 1)) ?></a>
        <?php else: ?>
            <a href="login.php" class="btn btn-light btn-sm">Ingresar</a>
        <?php endif; ?>
        <a href="tel:911" class="btn btn-danger btn-sm">✱ Llamada de Emergencia</a>
    </div>
</header>
