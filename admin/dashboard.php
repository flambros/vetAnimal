<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_veterinario();
$usuario = usuario_actual();
$activeAdmin = 'dashboard';

$hoy = date('Y-m-d');

$totalHoy = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE fecha = ? AND estado != 'cancelado'");
$totalHoy->execute([$hoy]);
$totalHoy = $totalHoy->fetchColumn();

$totalPacientes = $pdo->query('SELECT COUNT(*) FROM mascotas')->fetchColumn();
$totalClientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetchColumn();
$pendientes = $pdo->query("SELECT COUNT(*) FROM turnos WHERE estado='pendiente'")->fetchColumn();

$turnosHoy = $pdo->prepare("SELECT t.*, m.nombre AS mascota_nombre, u.nombre AS dueno, s.nombre AS servicio_nombre
                             FROM turnos t
                             JOIN mascotas m ON m.id = t.mascota_id
                             JOIN usuarios u ON u.id = m.usuario_id
                             JOIN servicios s ON s.id = t.servicio_id
                             WHERE t.fecha = ? AND t.estado != 'cancelado'
                             ORDER BY t.hora");
$turnosHoy->execute([$hoy]);
$turnosHoy = $turnosHoy->fetchAll();

$badge = ['pendiente' => 'badge-pendiente', 'confirmado' => 'badge-confirmado', 'cancelado' => 'badge-cancelado', 'completado' => 'badge-completado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Veterinario — VetAnimal</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-shell">
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 style="font-size:1.7rem;">Hola, <?= htmlspecialchars($usuario['nombre']) ?> 👋</h1>
                <p class="muted" style="color:var(--texto-muted);">Este es el resumen de la clínica para hoy, <?= date('d/m/Y') ?>.</p>
            </div>
            <a href="../booking.php" class="btn btn-outline btn-sm">Ver Turnos Públicos</a>
        </div>

        <div class="stat-grid">
            <div class="stat-card"><div class="num"><?= $totalHoy ?></div><div class="lbl">Turnos de Hoy</div></div>
            <div class="stat-card"><div class="num"><?= $pendientes ?></div><div class="lbl">Turnos Pendientes</div></div>
            <div class="stat-card"><div class="num"><?= $totalPacientes ?></div><div class="lbl">Pacientes Registrados</div></div>
            <div class="stat-card"><div class="num"><?= $totalClientes ?></div><div class="lbl">Clientes Activos</div></div>
        </div>

        <div class="panel-card">
            <h2>Agenda de Hoy</h2>
            <?php if (empty($turnosHoy)): ?>
                <p style="color:var(--texto-muted);">No hay turnos programados para hoy.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Hora</th><th>Paciente</th><th>Dueño</th><th>Servicio</th><th>Estado</th><th>Historial</th></tr></thead>
                    <tbody>
                    <?php foreach ($turnosHoy as $t): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($t['hora'])) ?></td>
                            <td><?= htmlspecialchars($t['mascota_nombre']) ?></td>
                            <td><?= htmlspecialchars($t['dueno']) ?></td>
                            <td><?= htmlspecialchars($t['servicio_nombre']) ?></td>
                            <td><span class="badge <?= $badge[$t['estado']] ?>"><?= ucfirst($t['estado']) ?></span></td>
                            <td><a href="../historial.php?mascota_id=<?= $t['mascota_id'] ?>" class="icon-btn" title="Ver historial">📋</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
