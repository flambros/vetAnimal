<?php
require_once __DIR__ . '/includes/auth.php';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $id = (int)$_POST['producto_id'];
    $_SESSION['carrito'][$id] = ($_SESSION['carrito'][$id] ?? 0) + 1;
    header('Location: tienda.php?agregado=1');
    exit;
}

if (isset($_GET['quitar'])) {
    unset($_SESSION['carrito'][(int)$_GET['quitar']]);
    header('Location: carrito.php');
    exit;
}

if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header('Location: carrito.php');
    exit;
}

$items = [];
$total = 0;
if (!empty($_SESSION['carrito'])) {
    $ids = array_keys($_SESSION['carrito']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($in)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $p) {
        $cant = $_SESSION['carrito'][$p['id']];
        $subtotal = $cant * $p['precio'];
        $total += $subtotal;
        $items[] = ['producto' => $p, 'cantidad' => $cant, 'subtotal' => $subtotal];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pedido'])) {
    requerir_login();
    $usuario = usuario_actual();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO pedidos (usuario_id, total, estado) VALUES (?, ?, "pagado")');
    $stmt->execute([$usuario['id'], $total]);
    $pedidoId = $pdo->lastInsertId();
    $stmtItem = $pdo->prepare('INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
    foreach ($items as $it) {
        $stmtItem->execute([$pedidoId, $it['producto']['id'], $it['cantidad'], $it['producto']['precio']]);
    }
    $pdo->commit();
    $_SESSION['carrito'] = [];
    header('Location: carrito.php?pedido=' . $pedidoId);
    exit;
}

$active = 'tienda';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="wizard-page">
    <h1 class="wizard-title" style="text-align:left;">Tu Carrito</h1>

    <?php if (isset($_GET['pedido'])): ?>
        <div class="alert alert-success" style="max-width:700px;">¡Pedido #<?= (int)$_GET['pedido'] ?> confirmado! Te avisaremos cuando esté listo para retirar.</div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="empty-state">
            Tu carrito está vacío.
            <div class="mt-20"><a href="tienda.php" class="btn btn-primary btn-sm">Ir a la Tienda</a></div>
        </div>
    <?php else: ?>
        <div class="wizard-panel" style="max-width:700px;">
            <table class="data-table" style="border:none;">
                <thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['producto']['nombre']) ?></td>
                        <td><?= $it['cantidad'] ?></td>
                        <td>$<?= number_format($it['subtotal'], 2) ?></td>
                        <td><a href="carrito.php?quitar=<?= $it['producto']['id'] ?>" class="link-accent" style="font-size:.85rem;">Quitar</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="flex" style="justify-content:space-between;align-items:center;margin-top:20px;">
                <strong style="font-size:1.2rem;">Total: $<?= number_format($total, 2) ?></strong>
                <form method="POST">
                    <button type="submit" name="confirmar_pedido" value="1" class="btn btn-primary">Confirmar Pedido</button>
                </form>
            </div>
            <a href="carrito.php?vaciar=1" class="link-accent" style="font-size:.85rem;">Vaciar carrito</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
