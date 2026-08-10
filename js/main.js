// ============================================================
// Interactividad general del sitio VetAnimal
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // Confirmaciones simples antes de acciones destructivas
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm)) e.preventDefault();
        });
    });

    // Filtro de categorías en la tienda
    var chips = document.querySelectorAll('.chip-filter');
    if (chips.length) {
        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (c) { c.classList.remove('active'); });
                chip.classList.add('active');
                var cat = chip.dataset.categoria;
                document.querySelectorAll('.product-card').forEach(function (card) {
                    card.style.display = (cat === 'todos' || card.dataset.categoria === cat) ? '' : 'none';
                });
            });
        });
    }

    // Búsqueda de productos en vivo
    var searchInput = document.getElementById('shop-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim().toLowerCase();
            document.querySelectorAll('.product-card').forEach(function (card) {
                var texto = card.dataset.nombre.toLowerCase();
                card.style.display = texto.includes(q) ? '' : 'none';
            });
        });
    }
});
