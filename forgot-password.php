<?php
require_once __DIR__ . '/includes/auth.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email !== '') {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $token = bin2hex(random_bytes(20));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare('UPDATE usuarios SET reset_token = ?, reset_token_expira = ? WHERE id = ?');
            $stmt->execute([$token, $expira, $usuario['id']]);
            // En producción, acá se enviaría un email con el link:
            // BASE_URL . '/reset-password.php?token=' . $token
        }
        // Mensaje genérico para no revelar si el email existe o no.
        $mensaje = 'Si el correo existe en nuestro sistema, te enviamos un enlace de recuperación.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restablecer Contraseña — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
<style>
  .reset-wrap{ min-height:100vh; display:flex; flex-direction:column; align-items:flex-start; padding:60px; background:var(--bg); }
  .reset-card{ background:#fff; border-radius:var(--radius-lg); box-shadow:var(--sombra-md); padding:50px; max-width:500px; width:100%; margin-top:60px; text-align:center; }
  .reset-card h1{ font-size:1.9rem; margin-bottom:16px; }
  .reset-card > p{ color:var(--texto-muted); margin-bottom:30px; }
  .reset-card .field{ text-align:left; }
</style>
</head>
<body>
<div class="reset-wrap">
    <span class="logo">Veterinaria VetAnimal</span>
    <div class="reset-card">
        <h1>Restablece tu contraseña</h1>
        <p>Ingresa tu dirección de correo electrónico y te enviaremos instrucciones para restablecer tu contraseña.</p>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="field">
                    <label>Dirección de Correo Electrónico</label>
                    <div class="input-wrap">
                        <span>✉</span>
                        <input type="email" name="email" placeholder="name@example.com" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">➤ Enviar enlace de recuperación</button>
            </form>
        <?php endif; ?>

        <div class="divider-text" style="margin:24px 0 0;"></div>
        <div class="auth-foot"><a href="login.php" class="link-accent">← Volver al inicio</a></div>
    </div>
    <div class="auth-copy" style="margin-top:40px;">© <?= date('Y') ?> Veterinaria VetAnimal. Todos los derechos reservados.</div>
</div>
</body>
</html>
