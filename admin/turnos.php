<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_veterinario();
$activeAdmin = 'turnos';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['turno_id'], $_POST['nuevo_estado'])) {
    $estadosValidos = ['pendiente', 'confirmado', 'cancelado', 'completado'];
    if (in_array($_POST['nuevo_estado'], $estadosValidos)) {
        $stmt = $pdo->prepare('UPDATE turnos SET estado = ? WHERE id = ?');
        $stmt->execute([$_POST['nuevo_estado'], (int)$_POST['turno_id']]);
    }
    header('Location: turnos.php' . (isset($_GET['fecha']) ? '?fecha=' . $_GET['fecha'] : ''));
    exit;
}

$filtroFecha = $_GET['fecha'] ?? '';
$sql = "SELECT t.*, m.nombre AS mascota_nombre, u.nombre AS dueno, s.nombre AS servicio_nombre
        FROM turnos t
        JOIN mascotas m ON m.id = t.mascota_id
        JOIN usuarios u ON u.id = m.usuario_id
        JOIN servicios s ON s.id = t.servicio_id";
$params = [];
if ($filtroFecha) { $sql .= ' WHERE t.fecha = ?'; $params[] = $filtroFecha; }
$sql .= ' ORDER BY t.fecha DESC, t.hora DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$turnos = $stmt->fetchAll();

$badge = ['pendiente' => 'badge-pendiente', 'confirmado' => 'badge-confirmado', 'cancelado' => 'badge-cancelado', 'completado' => 'badge-completado'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Turnos — Panel VetAnimal</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-shell">
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 style="font-size:1.7rem;">Todos los Turnos</h1>
            <form method="GET" class="flex gap-10">
                <div class="input-wrap" style="padding:8px 14px;">
                    <input type="date" name="fecha" value="<?= htmlspecialchars($filtroFecha) ?>" onchange="this.form.submit()">
                </div>
                <?php if ($filtroFecha): ?><a href="turnos.php" class="btn btn-light btn-sm">Ver todos</a><?php endif; ?>
            </form>
        </div>

        <div class="panel-card">
            <?php if (empty($turnos)): ?>
                <p style="color:var(--texto-muted);">No se encontraron turnos.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Dueño</th><th>Servicio</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($turnos as $t): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($t['fecha'])) ?></td>
                            <td><?= date('H:i', strtotime($t['hora'])) ?></td>
                            <td><?= htmlspecialchars($t['mascota_nombre']) ?></td>
                            <td><?= htmlspecialchars($t['dueno']) ?></td>
                            <td><?= htmlspecialchars($t['servicio_nombre']) ?></td>
                            <td><span class="badge <?= $badge[$t['estado']] ?>"><?= ucfirst($t['estado']) ?></span></td>
                            <td class="table-actions">
                                <form method="POST">
                                    <input type="hidden" name="turno_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="nuevo_estado" value="completado">
                                    <button type="submit" class="icon-btn" title="Marcar completado">✓</button>
                                </form>
                                <form method="POST" data-confirm="¿Cancelar este turno?">
                                    <input type="hidden" name="turno_id" value="<?= $t['id'] ?>">
                                    <input type="hidden" name="nuevo_estado" value="cancelado">
                                    <button type="submit" class="icon-btn" title="Cancelar">✕</button>
                                </form>
                                <a href="../historial.php?mascota_id=<?= $t['mascota_id'] ?>" class="icon-btn" title="Historial">📋</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
