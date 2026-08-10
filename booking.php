<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();

$usuario = usuario_actual();
if ($usuario['rol'] !== 'cliente') {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}

if (!isset($_SESSION['wizard'])) $_SESSION['wizard'] = [];
$wizard = &$_SESSION['wizard'];

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$step = max(1, min(3, $step));
$error = '';

// ---------- Datos base ----------
$mascotas = $pdo->prepare('SELECT * FROM mascotas WHERE usuario_id = ? ORDER BY nombre');
$mascotas->execute([$usuario['id']]);
$mascotas = $mascotas->fetchAll();

$servicios = $pdo->query('SELECT * FROM servicios ORDER BY nombre')->fetchAll();

$iconos = ['heart' => '♡', 'scalpel' => '🩹', 'flask' => '🧪', 'syringe' => '💉'];

// ---------- Paso 1: Mascota y Servicio ----------
if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mascotaId  = (int)($_POST['mascota_id'] ?? 0);
    $servicioId = (int)($_POST['servicio_id'] ?? 0);
    if (!$mascotaId || !$servicioId) {
        $error = 'Elegí una mascota y un servicio para continuar.';
    } else {
        $wizard['mascota_id']  = $mascotaId;
        $wizard['servicio_id'] = $servicioId;
        header('Location: booking.php?step=2');
        exit;
    }
}

// ---------- Paso 2: Fecha y hora ----------
if ($step === 2) {
    if (empty($wizard['mascota_id']) || empty($wizard['servicio_id'])) {
        header('Location: booking.php?step=1'); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fecha = $_POST['fecha'] ?? '';
        $hora  = $_POST['hora'] ?? '';
        if (!$fecha || !$hora) {
            $error = 'Elegí una fecha y un horario disponible.';
        } else {
            $wizard['fecha'] = $fecha;
            $wizard['hora']  = $hora;
            header('Location: booking.php?step=3');
            exit;
        }
    }
}

// ---------- Paso 3: Revisión y confirmación ----------
if ($step === 3) {
    if (empty($wizard['fecha']) || empty($wizard['hora'])) {
        header('Location: booking.php?step=2'); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $vetStmt = $pdo->query("SELECT id FROM usuarios WHERE rol='veterinario' ORDER BY RAND() LIMIT 1");
        $vet = $vetStmt->fetch();
        $veterinarioId = $vet ? $vet['id'] : null;
        $notas = trim($_POST['notas'] ?? '');

        try {
            $stmt = $pdo->prepare('INSERT INTO turnos (mascota_id, servicio_id, veterinario_id, fecha, hora, estado, notas) VALUES (?, ?, ?, ?, ?, "confirmado", ?)');
            $stmt->execute([$wizard['mascota_id'], $wizard['servicio_id'], $veterinarioId, $wizard['fecha'], $wizard['hora'], $notas]);
            $turnoId = $pdo->lastInsertId();
            unset($_SESSION['wizard']);
            header('Location: booking.php?confirmado=' . $turnoId);
            exit;
        } catch (PDOException $e) {
            $error = 'Ese horario ya fue reservado por otra persona. Elegí otro horario.';
        }
    }
}

// ---------- Confirmación final ----------
if (isset($_GET['confirmado'])) {
    $stmt = $pdo->prepare('SELECT t.*, m.nombre AS mascota_nombre, s.nombre AS servicio_nombre
                            FROM turnos t
                            JOIN mascotas m ON m.id = t.mascota_id
                            JOIN servicios s ON s.id = t.servicio_id
                            WHERE t.id = ?');
    $stmt->execute([(int)$_GET['confirmado']]);
    $turnoConfirmado = $stmt->fetch();
}

// ---------- Datos auxiliares para mostrar resumen ----------
$mascotaSel = null; $servicioSel = null;
foreach ($mascotas as $m) if ($m['id'] == ($wizard['mascota_id'] ?? 0)) $mascotaSel = $m;
foreach ($servicios as $s) if ($s['id'] == ($wizard['servicio_id'] ?? 0)) $servicioSel = $s;

// ---------- Calendario (paso 2) ----------
if ($step === 2) {
    $mesParam = $_GET['mes'] ?? date('Y-m');
    $ts = strtotime($mesParam . '-01');
    $anio = (int)date('Y', $ts);
    $mesNum = (int)date('n', $ts);
    $primerDiaSemana = (int)date('w', $ts); // 0=domingo
    $totalDias = (int)date('t', $ts);
    $mesesEs = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    $mesAnterior = date('Y-m', strtotime('-1 month', $ts));
    $mesSiguiente = date('Y-m', strtotime('+1 month', $ts));

    // Días con turnos ya existentes ese mes (para mostrar el puntito)
    $stmt = $pdo->prepare("SELECT DISTINCT DAY(fecha) as d FROM turnos WHERE DATE_FORMAT(fecha, '%Y-%m') = ? AND estado != 'cancelado'");
    $stmt->execute([$mesParam]);
    $diasConTurno = array_column($stmt->fetchAll(), 'd');

    $fechaSeleccionada = $_GET['fecha'] ?? ($wizard['fecha'] ?? '');
    $horariosBase = ['09:00','09:30','10:00','10:30','11:00','11:30','13:30','14:00','14:30','15:30','16:00'];
    $horariosOcupados = [];
    if ($fechaSeleccionada) {
        $stmt = $pdo->prepare("SELECT TIME_FORMAT(hora,'%H:%i') as h FROM turnos WHERE fecha = ? AND estado != 'cancelado'");
        $stmt->execute([$fechaSeleccionada]);
        $horariosOcupados = array_column($stmt->fetchAll(), 'h');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reservar Turno — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="app-header">
    <a href="index.php" class="logo">Veterinaria VetAnimal</a>
    <nav class="main-nav">
        <a href="index.php">Inicio</a>
        <a href="booking.php" class="active">Turnos</a>
        <a href="historial.php">Diagnósticos</a>
        <a href="tienda.php">Tienda</a>
    </nav>
    <div class="header-actions">
        <a href="index.php" class="back-link">← Volver</a>
        <a href="tel:911" class="btn btn-danger btn-sm">✱ Llamada de Emergencia</a>
    </div>
</header>

<div class="wizard-page">

<?php if (isset($turnoConfirmado)): ?>
    <div style="max-width:560px;margin:60px auto;text-align:center;">
        <div style="font-size:3.4rem;">✅</div>
        <h1 style="margin:18px 0 12px;">¡Turno confirmado!</h1>
        <p style="color:var(--texto-muted);margin-bottom:30px;">
            Reservamos el turno de <strong><?= htmlspecialchars($turnoConfirmado['servicio_nombre']) ?></strong>
            para <strong><?= htmlspecialchars($turnoConfirmado['mascota_nombre']) ?></strong>
            el <?= date('d/m/Y', strtotime($turnoConfirmado['fecha'])) ?> a las <?= date('H:i', strtotime($turnoConfirmado['hora'])) ?> hs.
        </p>
        <a href="historial.php" class="btn btn-primary">Ver Mis Turnos</a>
        <a href="index.php" class="btn btn-outline">Volver al Inicio</a>
    </div>

<?php else: ?>

    <div class="wizard-steps">
        <div class="wizard-step <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>">
            <div class="step-circle"><?= $step > 1 ? '✓' : '1' ?></div>
            <span class="label">Mascota y Servicio</span>
        </div>
        <div class="wizard-line <?= $step > 1 ? 'done' : '' ?>"></div>
        <div class="wizard-step <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>">
            <div class="step-circle"><?= $step > 2 ? '✓' : '2' ?></div>
            <span class="label">Fecha y Hora</span>
        </div>
        <div class="wizard-line <?= $step > 2 ? 'done' : '' ?>"></div>
        <div class="wizard-step <?= $step >= 3 ? 'active' : '' ?>">
            <div class="step-circle">3</div>
            <span class="label">Revisión</span>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="max-width:1100px;margin:0 auto 20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ===================== PASO 1 ===================== -->
    <?php if ($step === 1): ?>
        <h1 class="wizard-title">Elegí tu Mascota y Servicio</h1>
        <p class="wizard-sub">Seleccioná para quién es la consulta y qué tipo de atención necesita.</p>

        <form method="POST" id="form-paso1">
        <div class="wizard-grid" style="grid-template-columns:1fr;">
            <div class="wizard-panel">
                <h3 style="margin-bottom:16px;">Tu mascota</h3>
                <?php if (empty($mascotas)): ?>
                    <div class="empty-state">
                        Todavía no tenés mascotas registradas.
                        <div class="mt-20"><a href="mascota-nueva.php" class="btn btn-primary btn-sm">+ Agregar Mascota</a></div>
                    </div>
                <?php else: ?>
                    <div class="pet-select-grid">
                        <?php foreach ($mascotas as $m): ?>
                            <label class="option-card" data-group="mascota">
                                <input type="radio" name="mascota_id" value="<?= $m['id'] ?>" class="hidden" <?= (isset($wizard['mascota_id']) && $wizard['mascota_id'] == $m['id']) ? 'checked' : '' ?> required>
                                <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?w=200&q=80" alt="<?= htmlspecialchars($m['nombre']) ?>">
                                <div>
                                    <strong><?= htmlspecialchars($m['nombre']) ?></strong>
                                    <small><?= htmlspecialchars($m['especie']) ?> · <?= htmlspecialchars($m['raza']) ?></small>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <a href="mascota-nueva.php" class="link-accent" style="font-size:.88rem;">+ Agregar otra mascota</a>
                <?php endif; ?>

                <h3 style="margin:30px 0 16px;">Tipo de servicio</h3>
                <div class="service-select-grid">
                    <?php foreach ($servicios as $s): ?>
                        <label class="option-card" data-group="servicio">
                            <input type="radio" name="servicio_id" value="<?= $s['id'] ?>" class="hidden" <?= (isset($wizard['servicio_id']) && $wizard['servicio_id'] == $s['id']) ? 'checked' : '' ?> required>
                            <div class="icon-badge"><?= $iconos[$s['icono']] ?? '🩺' ?></div>
                            <div>
                                <strong><?= htmlspecialchars($s['nombre']) ?></strong>
                                <small><?= htmlspecialchars($s['descripcion']) ?></small>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="wizard-actions" style="justify-content:flex-end;">
            <button type="submit" class="btn btn-primary" <?= empty($mascotas) ? 'disabled' : '' ?>>Continuar →</button>
        </div>
        </form>

    <!-- ===================== PASO 2 ===================== -->
    <?php elseif ($step === 2): ?>
        <h1 class="wizard-title">Seleccionar Fecha y Hora</h1>
        <p class="wizard-sub">Elegí un horario conveniente para el <?= htmlspecialchars($servicioSel['nombre'] ?? '') ?> de <?= htmlspecialchars($mascotaSel['nombre'] ?? '') ?>.</p>

        <form method="POST" id="form-paso2">
        <input type="hidden" name="fecha" id="input-fecha" value="<?= htmlspecialchars($fechaSeleccionada) ?>">
        <input type="hidden" name="hora" id="input-hora" value="">

        <div class="wizard-grid">
            <div style="display:flex; gap:24px; flex-wrap:wrap;">
                <div class="calendar-panel" style="flex:1; min-width:280px;">
                    <div class="cal-head">
                        <h3><?= $mesesEs[$mesNum] ?> <?= $anio ?></h3>
                        <div class="cal-nav">
                            <a href="?step=2&mes=<?= $mesAnterior ?>"><button type="button">‹</button></a>
                            <a href="?step=2&mes=<?= $mesSiguiente ?>"><button type="button">›</button></a>
                        </div>
                    </div>
                    <div class="cal-grid">
                        <?php foreach (['Do','Lu','Ma','Mi','Ju','Vi','Sá'] as $d): ?>
                            <div class="dow"><?= $d ?></div>
                        <?php endforeach; ?>

                        <?php for ($i = 0; $i < $primerDiaSemana; $i++): ?>
                            <div class="cal-day muted"></div>
                        <?php endfor; ?>

                        <?php
                        $hoy = date('Y-m-d');
                        for ($d = 1; $d <= $totalDias; $d++):
                            $fechaDia = sprintf('%04d-%02d-%02d', $anio, $mesNum, $d);
                            $esPasado = $fechaDia < $hoy;
                            $clases = 'cal-day';
                            if ($esPasado) $clases .= ' muted';
                            if ($fechaDia === $hoy) $clases .= ' today';
                            if ($fechaDia === $fechaSeleccionada) $clases .= ' selected';
                            if (in_array($d, $diasConTurno)) $clases .= ' has-turnos';
                        ?>
                            <?php if ($esPasado): ?>
                                <div class="<?= $clases ?>"><?= $d ?></div>
                            <?php else: ?>
                                <a href="?step=2&mes=<?= $mesParam ?>&fecha=<?= $fechaDia ?>" class="<?= $clases ?>" style="text-decoration:none;"><?= $d ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="times-panel" style="flex:1; min-width:280px;">
                    <h3>Horarios Disponibles</h3>
                    <?php if (!$fechaSeleccionada): ?>
                        <p style="color:var(--texto-muted); margin-top:14px;">Seleccioná primero una fecha en el calendario.</p>
                    <?php else: ?>
                        <p style="color:var(--texto-muted); margin-top:6px;"><?= date('l d \d\e F', strtotime($fechaSeleccionada)) ?></p>
                        <div class="times-grid" id="times-grid">
                            <?php foreach ($horariosBase as $h): ?>
                                <?php $ocupado = in_array($h, $horariosOcupados); ?>
                                <div class="time-slot <?= $ocupado ? 'disabled' : '' ?>" data-hora="<?= $h ?>">
                                    <?= date('h:i A', strtotime($h)) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="summary-card">
                <h3>Resumen del Turno</h3>
                <div class="summary-row">
                    <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?w=200&q=80" alt="mascota">
                    <div>
                        <small>Paciente</small>
                        <strong><?= htmlspecialchars($mascotaSel['nombre'] ?? '') ?></strong>
                        <span class="detail"><?= htmlspecialchars($mascotaSel['especie'] ?? '') ?> · <?= htmlspecialchars($mascotaSel['raza'] ?? '') ?></span>
                    </div>
                </div>
                <div class="summary-divider"></div>
                <div class="summary-row">
                    <div class="icon-badge">🩺</div>
                    <div>
                        <small>Servicio</small>
                        <strong><?= htmlspecialchars($servicioSel['nombre'] ?? '') ?></strong>
                        <span class="detail"><?= htmlspecialchars($servicioSel['descripcion'] ?? '') ?></span>
                    </div>
                </div>
                <div class="summary-selected" id="summary-selected" style="<?= $fechaSeleccionada ? '' : 'display:none;' ?>">
                    <small>Horario Seleccionado</small>
                    <strong id="summary-fecha-hora"><?= $fechaSeleccionada ? date('D, d \d\e M', strtotime($fechaSeleccionada)) : '' ?></strong>
                    <div class="ok" id="summary-ok" style="<?= isset($_GET['hora']) ? '' : 'display:none;' ?>">✓ Selección Confirmada</div>
                </div>
            </div>
        </div>

        <div class="wizard-actions">
            <a href="booking.php?step=1" class="btn btn-outline">← Volver</a>
            <button type="submit" class="btn btn-primary" id="btn-continuar-2" disabled>Continuar →</button>
        </div>
        </form>

    <!-- ===================== PASO 3 ===================== -->
    <?php elseif ($step === 3): ?>
        <h1 class="wizard-title">Revisá tu Turno</h1>
        <p class="wizard-sub">Confirmá los datos antes de reservar.</p>

        <form method="POST">
        <div class="wizard-grid">
            <div class="wizard-panel">
                <div class="review-block">
                    <h4>Paciente</h4>
                    <div class="summary-row" style="margin-bottom:0;">
                        <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?w=200&q=80" alt="mascota">
                        <div>
                            <strong><?= htmlspecialchars($mascotaSel['nombre']) ?></strong>
                            <span class="detail"><?= htmlspecialchars($mascotaSel['especie']) ?> · <?= htmlspecialchars($mascotaSel['raza']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="review-block">
                    <h4>Servicio</h4>
                    <p><strong><?= htmlspecialchars($servicioSel['nombre']) ?></strong> — <?= htmlspecialchars($servicioSel['descripcion']) ?></p>
                </div>
                <div class="review-block">
                    <h4>Fecha y hora</h4>
                    <p><strong><?= date('l d \d\e F, Y', strtotime($wizard['fecha'])) ?></strong> a las <?= date('h:i A', strtotime($wizard['hora'])) ?></p>
                </div>
                <div class="review-block">
                    <h4>Notas para el equipo (opcional)</h4>
                    <textarea name="notas" class="wizard-notes" placeholder="Contanos si hay algo importante que debamos saber..."></textarea>
                </div>
            </div>

            <div class="summary-card">
                <h3>Resumen del Turno</h3>
                <div class="summary-row">
                    <div class="icon-badge">🐾</div>
                    <div><small>Paciente</small><strong><?= htmlspecialchars($mascotaSel['nombre']) ?></strong></div>
                </div>
                <div class="summary-row">
                    <div class="icon-badge">🩺</div>
                    <div><small>Servicio</small><strong><?= htmlspecialchars($servicioSel['nombre']) ?></strong></div>
                </div>
                <div class="summary-selected">
                    <small>Turno</small>
                    <strong><?= date('D, d \d\e M', strtotime($wizard['fecha'])) ?> · <?= date('h:i A', strtotime($wizard['hora'])) ?></strong>
                </div>
            </div>
        </div>

        <div class="wizard-actions">
            <a href="booking.php?step=2" class="btn btn-outline">← Volver</a>
            <button type="submit" class="btn btn-primary">Confirmar Turno ✓</button>
        </div>
        </form>
    <?php endif; ?>

<?php endif; ?>
</div>

<script src="js/main.js"></script>
<script src="js/booking.js"></script>
</body>
</html>
