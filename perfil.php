<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();
$usuario = usuario_actual();

$turnos = $pdo->prepare('SELECT t.*, m.nombre AS mascota_nombre, s.nombre AS servicio_nombre
                          FROM turnos t
                          JOIN mascotas m ON m.id = t.mascota_id
                          JOIN servicios s ON s.id = t.servicio_id
                          WHERE m.usuario_id = ?
                          ORDER BY t.fecha DESC, t.hora DESC');
$turnos->execute([$usuario['id']]);
$turnos = $turnos->fetchAll();

$badge = ['pendiente' => 'badge-pendiente', 'confirmado' => 'badge-confirmado', 'cancelado' => 'badge-cancelado', 'completado' => 'badge-completado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Cuenta — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wizard-page">
    <h1 class="wizard-title" style="text-align:left;">Mi Cuenta</h1>

    <div class="panel-card" style="max-width:800px;">
        <h2>Datos personales</h2>
        <p><strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($usuario['telefono'] ?: '—') ?></p>
    </div>

    <div class="panel-card" style="max-width:800px;">
        <h2>Mis Turnos</h2>
        <?php if (empty($turnos)): ?>
            <p class="muted">Todavía no reservaste ningún turno. <a href="booking.php" class="link-accent">Reservar ahora</a></p>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Mascota</th><th>Servicio</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead>
                <tbody>
                <?php foreach ($turnos as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['mascota_nombre']) ?></td>
                        <td><?= htmlspecialchars($t['servicio_nombre']) ?></td>
                        <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                        <td><?= date('H:i', strtotime($t['hora'])) ?></td>
                        <td><span class="badge <?= $badge[$t['estado']] ?>"><?= ucfirst($t['estado']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
