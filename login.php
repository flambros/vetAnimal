<?php
require_once __DIR__ . '/includes/auth.php';

if (esta_logueado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $error = 'Completá tu email y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($pass, $usuario['password_hash'])) {
            iniciar_sesion_usuario($usuario);
            if ($usuario['rol'] === 'veterinario') {
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
            } else {
                header('Location: ' . BASE_URL . '/index.php');
            }
            exit;
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingresar — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-visual" style="background-image:url('https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=1000&q=80');">
        <span class="logo">Veterinaria VetAnimal</span>
        <h2>Compassionate Care<br>for Every Companion.</h2>
        <p>Your pet's health and happiness are our top priority.</p>
    </div>
    <div class="auth-form-side">
        <div class="auth-form">
            <h1>¡Hola de nuevo!</h1>
            <p>Iniciá sesión para gestionar turnos, ver registros y conectarte con tu equipo de atención.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registrado'])): ?>
                <div class="alert alert-success">Cuenta creada con éxito. Ya podés iniciar sesión.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label>Dirección de Email</label>
                    <div class="input-wrap">
                        <span>✉</span>
                        <input type="email" name="email" placeholder="nombre@ejemplo.com" required>
                    </div>
                </div>
                <div class="field">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <span>🔒</span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="field-row">
                    <label class="checkbox-row"><input type="checkbox" name="recordarme"> Recordarme</label>
                    <a href="forgot-password.php" class="link-accent">¿Olvidaste tu contraseña?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>

            <div class="divider-text">O continúa con</div>
            <button type="button" class="oauth-btn" onclick="alert('Integración disponible próximamente.')"> Iniciar sesión con Google</button>
            <button type="button" class="oauth-btn" onclick="alert('Integración disponible próximamente.')"> Iniciar sesión con Apple</button>

            <div class="auth-foot">¿No tienes una cuenta? <a href="register.php" class="link-accent">Registrarse</a></div>
            <div class="auth-copy">© <?= date('Y') ?> Veterinaria VetAnimal. Todos los derechos reservados.</div>

            <div class="alert" style="margin-top:26px;background:var(--bg-alt);color:var(--texto-muted);">
                <strong>Cuentas de prueba</strong><br>
                Cliente: agustina.gomez@example.com<br>
                Veterinario: santiago.mendez@vetanimal.com<br>
                Contraseña para ambas: 123456
            </div>
        </div>
    </div>
</div>
</body>
</html>
