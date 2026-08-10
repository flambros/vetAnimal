<?php
require_once __DIR__ . '/includes/auth.php';
requerir_login();
$usuario = usuario_actual();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $raza = trim($_POST['raza'] ?? '');
    $edad = $_POST['edad'] !== '' ? (int)$_POST['edad'] : null;
    $peso = $_POST['peso'] !== '' ? (float)$_POST['peso'] : null;

    if ($nombre === '' || $especie === '') {
        $error = 'El nombre y la especie son obligatorios.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO mascotas (usuario_id, nombre, especie, raza, edad, peso) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$usuario['id'], $nombre, $especie, $raza, $edad, $peso]);
        header('Location: booking.php?step=1');
        exit;
    }
}
$active = 'turnos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agregar Mascota — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wizard-page">
    <h1 class="wizard-title">Agregar Mascota</h1>
    <p class="wizard-sub">Contanos sobre tu compañero para poder brindarle la mejor atención.</p>

    <div class="wizard-panel" style="max-width:560px;margin:0 auto;">
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-grid">
                <div class="field">
                    <label>Nombre</label>
                    <div class="input-wrap"><input type="text" name="nombre" required></div>
                </div>
                <div class="field">
                    <label>Especie</label>
                    <div class="input-wrap">
                        <select name="especie" required>
                            <option value="">Elegir…</option>
                            <option>Perro</option>
                            <option>Gato</option>
                            <option>Ave</option>
                            <option>Conejo</option>
                            <option>Otro</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>Raza</label>
                    <div class="input-wrap"><input type="text" name="raza"></div>
                </div>
                <div class="field">
                    <label>Edad (años)</label>
                    <div class="input-wrap"><input type="number" name="edad" min="0" max="40"></div>
                </div>
                <div class="field">
                    <label>Peso (kg)</label>
                    <div class="input-wrap"><input type="number" step="0.1" name="peso" min="0"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-20">Guardar Mascota</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
