<?php
require_once __DIR__ . '/includes/auth.php';

if (esta_logueado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $tel    = trim($_POST['telefono'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $pass2  = $_POST['password2'] ?? '';

    if ($nombre === '' || $email === '' || $pass === '') {
        $error = 'Completá todos los campos obligatorios.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Ya existe una cuenta con ese email.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol, telefono) VALUES (?, ?, ?, "cliente", ?)');
            $stmt->execute([$nombre, $email, $hash, $tel]);
            header('Location: ' . BASE_URL . '/login.php?registrado=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Cuenta — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-visual" style="background-image:url('https://images.unsplash.com/photo-1544568100-847a948585b9?w=1000&q=80');">
        <span class="logo">Veterinaria VetAnimal</span>
        <h2>Sumate a la<br>familia VetAnimal.</h2>
        <p>Creá tu cuenta para reservar turnos y llevar el historial de tu mascota.</p>
    </div>
    <div class="auth-form-side">
        <div class="auth-form">
            <h1>Creá tu cuenta</h1>
            <p>Es rápido y gratuito. Empezá a cuidar a tu compañero hoy mismo.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Nombre Completo</label>
                    <div class="input-wrap">
                        <span>👤</span>
                        <input type="text" name="nombre" placeholder="Tu nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Dirección de Email</label>
                    <div class="input-wrap">
                        <span>✉</span>
                        <input type="email" name="email" placeholder="nombre@ejemplo.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Teléfono</label>
                    <div class="input-wrap">
                        <span>📞</span>
                        <input type="text" name="telefono" placeholder="11-1234-5678" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                    </div>
                </div>
                <div class="field">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <span>🔒</span>
                        <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                    </div>
                </div>
                <div class="field">
                    <label>Confirmar Contraseña</label>
                    <div class="input-wrap">
                        <span>🔒</span>
                        <input type="password" name="password2" placeholder="Repetí tu contraseña" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Crear Cuenta</button>
            </form>

            <div class="auth-foot">¿Ya tenés una cuenta? <a href="login.php" class="link-accent">Iniciar Sesión</a></div>
            <div class="auth-copy">© <?= date('Y') ?> Veterinaria VetAnimal. Todos los derechos reservados.</div>
        </div>
    </div>
</div>
</body>
</html>
