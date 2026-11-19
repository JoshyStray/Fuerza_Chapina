// Comportamiento común de la interfaz. Sin dependencias externas (funciona sin internet).
document.addEventListener('DOMContentLoaded', () => {
    // 1) Confirmación antes de acciones destructivas (formularios con data-confirm).
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!window.confirm(form.dataset.confirm)) {
                evento.preventDefault();
            }
        });
    });

    // 2) Evita envíos duplicados: al enviar, los botones del formulario se deshabilitan.
    document.querySelectorAll('form[method="post"]').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (evento.defaultPrevented) {
                return;
            }
            form.querySelectorAll('button[type="submit"]').forEach((boton) => {
                boton.disabled = true;
            });
        });
    });

    // 3) Membresías: al elegir un plan se sugiere su precio como monto pagado.
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

    // 4) Reportes: botón para imprimir (el CSS oculta menús y filtros al imprimir).
    document.querySelectorAll('[data-accion="imprimir"]').forEach((boton) => {
        boton.addEventListener('click', () => window.print());
        
    });
});

