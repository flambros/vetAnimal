<?php
require_once __DIR__ . '/includes/auth.php';
requerir_veterinario();
$usuario = usuario_actual();

$mascotaId = (int)($_GET['mascota_id'] ?? $_POST['mascota_id'] ?? 0);
$stmt = $pdo->prepare('SELECT m.*, u.nombre AS dueno FROM mascotas m JOIN usuarios u ON u.id = m.usuario_id WHERE m.id = ?');
$stmt->execute([$mascotaId]);
$mascota = $stmt->fetch();
if (!$mascota) { header('Location: admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? 'CONTROL';
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    if ($titulo === '' || $descripcion === '') {
        $error = 'Completá el título y la descripción del registro.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO consultas (mascota_id, veterinario_id, fecha, tipo, titulo, descripcion) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$mascotaId, $usuario['id'], $fecha, $tipo, $titulo, $descripcion]);
        header('Location: historial.php?mascota_id=' . $mascotaId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo Registro Clínico — VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="app-header">
    <a href="admin/dashboard.php" class="logo">VetAnimal</a>
    <a href="historial.php?mascota_id=<?= $mascotaId ?>" class="back-link">← Volver al historial</a>
</header>

<div class="wizard-page">
    <h1 class="wizard-title">Nuevo Registro Clínico</h1>
    <p class="wizard-sub">Paciente: <strong><?= htmlspecialchars($mascota['nombre']) ?></strong> — Dueño: <?= htmlspecialchars($mascota['dueno']) ?></p>

    <div class="wizard-panel" style="max-width:680px;margin:0 auto;">
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="mascota_id" value="<?= $mascotaId ?>">
            <div class="form-grid">
                <div class="field">
                    <label>Tipo de registro</label>
                    <div class="input-wrap">
                        <select name="tipo">
                            <option value="CONTROL">Control</option>
                            <option value="EMERGENCIA">Emergencia</option>
                            <option value="VACUNA">Vacuna</option>
                            <option value="CIRUGIA">Cirugía</option>
                            <option value="DIAGNOSTICO">Diagnóstico</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>Fecha</label>
                    <div class="input-wrap"><input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required></div>
                </div>
            </div>
            <div class="field">
                <label>Título</label>
                <div class="input-wrap"><input type="text" name="titulo" placeholder="Ej: Control Anual Preventivo" required></div>
            </div>
            <div class="field">
                <label>Descripción</label>
                <textarea name="descripcion" class="wizard-notes" style="min-height:140px;" required placeholder="Detalle de la consulta, tratamiento aplicado, indicaciones..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Guardar Registro</button>
        </form>
    </div>
</div>
