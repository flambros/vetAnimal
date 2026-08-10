<?php
// Espera $activeAdmin = 'dashboard'|'turnos'|'pacientes'
$usuario = usuario_actual();
?>
<div class="admin-sidebar">
    <span class="logo">Veterinaria VetAnimal</span>
    <nav class="admin-nav">
        <a href="dashboard.php" class="<?= ($activeAdmin ?? '') === 'dashboard' ? 'active' : '' ?>">📊 Panel Principal</a>
        <a href="turnos.php" class="<?= ($activeAdmin ?? '') === 'turnos' ? 'active' : '' ?>">📅 Turnos</a>
        <a href="pacientes.php" class="<?= ($activeAdmin ?? '') === 'pacientes' ? 'active' : '' ?>">🐾 Pacientes</a>
    </nav>
    <div style="margin-top:auto;">
        <a href="../index.php" style="display:block;color:rgba(255,255,255,.78);font-size:.85rem;margin-bottom:10px;">← Ver sitio público</a>
        <a href="../logout.php" style="display:block;color:rgba(255,255,255,.78);font-size:.85rem;">Cerrar sesión</a>
    </div>
</div>
