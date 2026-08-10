// ============================================================
// Interactividad del asistente de reserva de turnos
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // ---- Paso 1: tarjetas seleccionables (mascota / servicio) ----
    document.querySelectorAll('.option-card').forEach(function (card) {
        var input = card.querySelector('input[type="radio"]');
        if (!input) return;

        var group = card.dataset.group;

        var syncSelected = function () {
            document.querySelectorAll('.option-card[data-group="' + group + '"]').forEach(function (c) {
                c.classList.remove('selected');
            });
            if (input.checked) card.classList.add('selected');
        };

        if (input.checked) card.classList.add('selected');

        card.addEventListener('click', function () {
            input.checked = true;
            document.querySelectorAll('.option-card[data-group="' + group + '"]').forEach(function (c) {
                c.classList.remove('selected');
            });
            card.classList.add('selected');
        });
    });

    // ---- Paso 2: selección de horario ----
    var timesGrid = document.getElementById('times-grid');
    var inputHora = document.getElementById('input-hora');
    var btnContinuar = document.getElementById('btn-continuar-2');
    var summaryBox = document.getElementById('summary-selected');
    var summaryOk = document.getElementById('summary-ok');
    var summaryFechaHora = document.getElementById('summary-fecha-hora');

    if (timesGrid) {
        timesGrid.querySelectorAll('.time-slot').forEach(function (slot) {
            if (slot.classList.contains('disabled')) return;
            slot.addEventListener('click', function () {
                timesGrid.querySelectorAll('.time-slot').forEach(function (s) { s.classList.remove('selected'); });
                slot.classList.add('selected');
                inputHora.value = slot.dataset.hora;
                if (btnContinuar) btnContinuar.disabled = false;
                if (summaryBox) summaryBox.style.display = 'block';
                if (summaryOk) summaryOk.style.display = 'block';
                if (summaryFechaHora) {
                    summaryFechaHora.textContent = summaryFechaHora.textContent.split(' · ')[0] + ' · ' + slot.textContent.trim();
                }
            });
        });
    }

    // Si ya hay fecha elegida pero no hora, el botón continuar queda deshabilitado
    // hasta que el usuario haga click en un horario disponible.
});
