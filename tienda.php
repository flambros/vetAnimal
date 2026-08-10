<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'tienda';

$productos = $pdo->query('SELECT * FROM productos ORDER BY id')->fetchAll();

$categoriaSlug = function ($cat) {
    return ['Medicamentos' => 'medicamentos', 'Bienestar y Estética' => 'bienestar', 'Nutrición y Alimento' => 'nutricion', 'Pulgas y Garrapatas' => 'pulgas'][$cat] ?? 'otros';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda — Veterinaria VetAnimal</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="shop-hero">
    <div>
        <h1>Esenciales Premium para el Cuidado de tu Mascota</h1>
        <p>Medicamentos aprobados clínicamente, alimentos altamente nutritivos y productos de bienestar diario adaptados para la salud óptima de tu compañero. Con la confianza de nuestros veterinarios.</p>
        <div class="search-bar">
            <span>🔍</span>
            <input type="text" id="shop-search" placeholder="Buscar medicamentos, comida o bienestar…">
        </div>
    </div>
    <img src="https://images.unsplash.com/photo-1583947581924-860bda6a26df?w=600&q=80" alt="Productos VetAnimal">
</div>

<div class="category-bar">
    <button class="chip-filter active" data-categoria="todos">Todos los Productos</button>
    <button class="chip-filter" data-categoria="medicamentos">Medicamentos</button>
    <button class="chip-filter" data-categoria="bienestar">Bienestar y Estética</button>
    <button class="chip-filter" data-categoria="nutricion">Nutrición y Alimento</button>
    <button class="chip-filter" data-categoria="pulgas">Pulgas y Garrapatas</button>
</div>

<div class="product-grid">
    <?php foreach ($productos as $p): ?>
        <div class="product-card" data-categoria="<?= $categoriaSlug($p['categoria']) ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="product-thumb">
                <?php if ($p['etiqueta']): ?><span class="tag"><?= htmlspecialchars($p['etiqueta']) ?></span><?php endif; ?>
                <span style="font-size:2.4rem;">🧴</span>
            </div>
            <div class="product-body">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <p><?= htmlspecialchars($p['descripcion']) ?></p>
                <div class="product-foot">
                    <span class="price">$<?= number_format($p['precio'], 2) ?></span>
                    <form method="POST" action="carrito.php" style="margin:0;">
                        <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="cart-btn" title="Agregar al carrito">🛒</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
