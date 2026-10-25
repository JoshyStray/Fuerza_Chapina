document.addEventListener('DOMContentLoaded', () => {
    // Confirmación antes de enviar formularios marcados con data-confirm (p. ej. eliminar).
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!window.confirm(form.dataset.confirm)) {
                evento.preventDefault();
            }
        });
    });

    // Al elegir un plan, sugiere su precio como monto pagado.
    const plan = document.getElementById('plan_id');
    const monto = document.getElementById('monto_pagado');
    if (plan && monto) {
        plan.addEventListener('change', () => {
            const opcion = plan.selectedOptions[0];
            if (opcion && opcion.dataset.precio) {
                monto.value = opcion.dataset.precio;
            }
        });
    }
});