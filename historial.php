<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();
$usuario = usuario_actual();
$active = 'diagnosticos';

if ($usuario['rol'] === 'veterinario') {
    // El veterinario ve el historial desde el panel admin con mascota_id específica
    $mascotaId = (int)($_GET['mascota_id'] ?? 0);
    if (!$mascotaId) { header('Location: ' . BASE_URL . '/admin/dashboard.php'); exit; }
    $stmt = $pdo->prepare('SELECT m.*, u.nombre AS dueno FROM mascotas m JOIN usuarios u ON u.id = m.usuario_id WHERE m.id = ?');
    $stmt->execute([$mascotaId]);
    $mascota = $stmt->fetch();
    $mascotas = [$mascota];
} else {
    $mascotas = $pdo->prepare('SELECT * FROM mascotas WHERE usuario_id = ? ORDER BY nombre');
    $mascotas->execute([$usuario['id']]);
    $mascotas = $mascotas->fetchAll();
    $mascotaId = (int)($_GET['mascota_id'] ?? ($mascotas[0]['id'] ?? 0));
    $mascota = null;
    foreach ($mascotas as $m) if ($m['id'] == $mascotaId) $mascota = $m;
}

if (!$mascota) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="wizard-page"><div class="empty-state">Todavía no tenés mascotas registradas.<div class="mt-20"><a href="mascota-nueva.php" class="btn btn-primary btn-sm">+ Agregar Mascota</a></div></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$consultas = $pdo->prepare('SELECT c.*, u.nombre AS vet_nombre FROM consultas c LEFT JOIN usuarios u ON u.id = c.veterinario_id WHERE c.mascota_id = ? ORDER BY c.fecha DESC');
$consultas->execute([$mascotaId]);
$consultas = $consultas->fetchAll();

$vacunas = $pdo->prepare('SELECT * FROM vacunas WHERE mascota_id = ? ORDER BY fecha_refuerzo DESC');
$vacunas->execute([$mascotaId]);
$vacunas = $vacunas->fetchAll();

$estudios = $pdo->prepare('SELECT * FROM estudios WHERE mascota_id = ? ORDER BY fecha DESC');
$estudios->execute([$mascotaId]);
$estudios = $estudios->fetchAll();

$tagClass = ['CONTROL' => 'tag-control', 'EMERGENCIA' => 'tag-emergencia', 'VACUNA' => 'tag-vacuna', 'CIRUGIA' => 'tag-control', 'DIAGNOSTICO' => 'tag-vacuna'];
$tagLabel = ['CONTROL' => 'CONTROL', 'EMERGENCIA' => 'EMERGENCIA', 'VACUNA' => 'VACUNA', 'CIRUGIA' => 'CIRUGÍA', 'DIAGNOSTICO' => 'DIAGNÓSTICO'];
$itemClass = ['CONTROL' => '', 'EMERGENCIA' => 'emergencia', 'VACUNA' => 'vacuna', 'CIRUGIA' => '', 'DIAGNOSTICO' => 'vacuna'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial Clínico de <?= htmlspecialchars($mascota['nombre']) ?> — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if ($usuario['rol'] !== 'veterinario'): ?>
    <?php include __DIR__ . '/includes/header.php'; ?>
<?php else: ?>
<header class="app-header">
    <a href="admin/dashboard.php" class="logo">VetAnimal</a>
    <nav class="main-nav">
        <a href="booking.php">Turnos</a>
        <a href="admin/dashboard.php">Panel</a>
    </nav>
    <a href="admin/dashboard.php" class="back-link">← Volver al Panel</a>
</header>
<?php endif; ?>

<div class="wizard-page" style="padding-top:36px;">
    <div class="hist-title-row">
        <div>
            <h1>Historial Clínico de <?= htmlspecialchars($mascota['nombre']) ?></h1>
            <span class="muted">ID de paciente: #BA-<?= str_pad($mascota['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="flex gap-10">
            <?php if (count($mascotas) > 1 && $usuario['rol'] !== 'veterinario'): ?>
                <form method="GET" style="margin-right:auto;">
                    <div class="input-wrap" style="padding:8px 14px;">
                        <select name="mascota_id" onchange="this.form.submit()">
                            <?php foreach ($mascotas as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $m['id'] == $mascotaId ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            <?php endif; ?>
            <a href="#" class="btn btn-light btn-sm" onclick="window.print();return false;">⬇ Descargar Historial Completo</a>
            <?php if ($usuario['rol'] === 'veterinario'): ?>
                <a href="registro-nuevo.php?mascota_id=<?= $mascotaId ?>" class="btn btn-primary btn-sm">+ Nuevo Registro</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hist-grid">
        <div class="pet-card">
            <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?w=500&q=80" alt="<?= htmlspecialchars($mascota['nombre']) ?>">
            <div class="pet-info">
                <div class="pet-info-row"><span>Nombre</span><strong><?= htmlspecialchars($mascota['nombre']) ?></strong></div>
                <div class="pet-info-row"><span>Raza</span><strong><?= htmlspecialchars($mascota['raza'] ?: '—') ?></strong></div>
                <div class="pet-info-row"><span>Edad</span><strong><?= $mascota['edad'] !== null ? $mascota['edad'] . ' años' : '—' ?></strong></div>
                <div class="pet-info-row"><span>Peso</span><strong><?= $mascota['peso'] !== null ? $mascota['peso'] . ' kg' : '—' ?></strong></div>
            </div>
        </div>

        <div class="timeline-card">
            <div class="timeline-head">
                <h3>🕓 Consultas Recientes</h3>
            </div>
            <?php if (empty($consultas)): ?>
                <p class="muted">Todavía no hay consultas registradas.</p>
            <?php endif; ?>
            <?php foreach ($consultas as $c): ?>
                <div class="timeline-item <?= $itemClass[$c['tipo']] ?? '' ?>">
                    <div class="timeline-dot"></div>
                    <div class="timeline-body">
                        <div class="timeline-top">
                            <div>
                                <div class="timeline-date"><?= strtoupper(date('d M Y', strtotime($c['fecha']))) ?></div>
                                <h4><?= htmlspecialchars($c['titulo']) ?></h4>
                            </div>
                            <span class="tag <?= $tagClass[$c['tipo']] ?? 'tag-control' ?>"><?= $tagLabel[$c['tipo']] ?? $c['tipo'] ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($c['descripcion'])) ?></p>
                        <?php if ($c['vet_nombre']): ?><div class="timeline-doc">👤 <?= htmlspecialchars($c['vet_nombre']) ?></div><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <h3>⚕ Resumen de Salud</h3>
            <p class="muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Estado Actual</p>
            <span class="status-pill"><?= strtoupper(htmlspecialchars($mascota['estado_salud'])) ?></span>

            <p class="muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin:20px 0 4px;">Alergias</p>
            <?php if ($mascota['alergias']): ?>
                <?php foreach (explode(',', $mascota['alergias']) as $a): ?>
                    <span class="chip"><?= htmlspecialchars(trim($a)) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:.9rem;">Ninguna registrada.</p>
            <?php endif; ?>

            <p class="muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;margin:20px 0 4px;">Condiciones Crónicas</p>
            <p style="font-size:.9rem;"><?= htmlspecialchars($mascota['condiciones_cronicas']) ?></p>
        </div>

        <div class="info-card">
            <h3>💉 Vacunación</h3>
            <?php foreach ($vacunas as $v): ?>
                <div class="mini-card">
                    <div>
                        <strong><?= htmlspecialchars($v['nombre']) ?></strong>
                        <small class="<?= $v['estado'] === 'VENCIDA' ? 'warn' : '' ?>">
                            <?= $v['estado'] === 'VENCIDA' ? 'Vencida' : 'Refuerzo' ?>: <?= $v['fecha_refuerzo'] ? date('d M Y', strtotime($v['fecha_refuerzo'])) : '—' ?>
                        </small>
                    </div>
                    <span><?= $v['estado'] === 'VENCIDA' ? '⚠️' : '✅' ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($vacunas)): ?><p class="muted">Sin registros de vacunación.</p><?php endif; ?>
        </div>

        <div class="info-card">
            <h3>🔬 Estudios &amp; Lab</h3>
            <?php foreach ($estudios as $e): ?>
                <div class="mini-card">
                    <div>
                        <strong><?= htmlspecialchars($e['nombre']) ?></strong>
                        <small><?= date('d M Y', strtotime($e['fecha'])) ?></small>
                    </div>
                    <span>›</span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($estudios)): ?><p class="muted">Sin estudios registrados.</p><?php endif; ?>
        </div>
    </div>
</div>

<?php if ($usuario['rol'] !== 'veterinario') include __DIR__ . '/includes/footer.php'; else echo '</body></html>'; ?>
