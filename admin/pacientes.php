<?php
require_once __DIR__ . '/../includes/auth.php';
requerir_veterinario();
$activeAdmin = 'pacientes';

$mascotas = $pdo->query("SELECT m.*, u.nombre AS dueno, u.telefono
                          FROM mascotas m JOIN usuarios u ON u.id = m.usuario_id
                          ORDER BY m.nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pacientes — Panel VetAnimal</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-shell">
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1 style="font-size:1.7rem;">Pacientes</h1>
        </div>

        <div class="panel-card">
            <table class="data-table">
                <thead><tr><th>Nombre</th><th>Especie / Raza</th><th>Edad</th><th>Peso</th><th>Dueño</th><th>Contacto</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($mascotas as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['nombre']) ?></td>
                        <td><?= htmlspecialchars($m['especie']) ?> · <?= htmlspecialchars($m['raza'] ?: '—') ?></td>
                        <td><?= $m['edad'] !== null ? $m['edad'] . ' años' : '—' ?></td>
                        <td><?= $m['peso'] !== null ? $m['peso'] . ' kg' : '—' ?></td>
                        <td><?= htmlspecialchars($m['dueno']) ?></td>
                        <td><?= htmlspecialchars($m['telefono'] ?: '—') ?></td>
                        <td><a href="../historial.php?mascota_id=<?= $m['id'] ?>" class="btn btn-light btn-sm">Ver Historial</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
