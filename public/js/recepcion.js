// Pantalla de recepción: consulta AJAX (fetch + JSON) del estado de la membresía.
(() => {
    'use strict';

    const campo = document.getElementById('codigo');
    const panel = document.getElementById('panel-socio');
    const boton = document.getElementById('btn-registrar');
    const lista = document.getElementById('lista-asistencias');
    const filaVacia = document.getElementById('fila-vacia');
    const total = document.getElementById('total-hoy');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const ui = {
        estado: document.getElementById('sem-estado'),
        nombre: document.getElementById('sem-nombre'),
        plan: document.getElementById('sem-plan'),
        vence: document.getElementById('sem-vence'),
        dias: document.getElementById('sem-dias'),
        mensaje: document.getElementById('sem-mensaje'),
    };

    const TITULOS = {
        vigente: 'Membresía vigente',
        vencida: 'Membresía vencida',
        programada: 'Aún no inicia',
        sin_membresia: 'Sin membresía',
        inactivo: 'Socio inactivo',
    };

    let temporizador = null;
    let controlador = null;
    let codigoActual = '';

    function pintar({ clase, estado, nombre = '—', plan = '—', vence = '—', dias = '—', mensaje = '', registrable = false }) {
        panel.className = `semaforo semaforo--${clase} mt-4`;
        ui.estado.textContent = estado;
        ui.nombre.textContent = nombre;
        ui.plan.textContent = plan;
        ui.vence.textContent = vence;
        ui.dias.textContent = dias;
        ui.mensaje.textContent = mensaje;
        boton.disabled = !registrable;
    }

    function pintarEspera() {
        codigoActual = '';
        pintar({ clase: 'espera', estado: 'Esperando código', mensaje: 'Digita el código del socio para ver su membresía.' });
    }

    function textoDias(socio) {
        if (socio.dias_restantes === null) {
            return '—';
        }
        if (socio.dias_restantes < 0) {
            return `Hace ${Math.abs(socio.dias_restantes)} d`;
        }
        return `${socio.dias_restantes} d`;
    }

    async function consultar(codigo) {
        if (controlador) {
            controlador.abort();
        }
        controlador = new AbortController();

        try {
            const respuesta = await fetch(`${campo.dataset.urlConsultar}&codigo=${encodeURIComponent(codigo)}`, {
                headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' },
                credentials: 'same-origin',
                signal: controlador.signal,
            });
            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                pintar({ clase: 'espera', estado: 'Aviso', mensaje: datos.mensaje || 'No se pudo consultar.' });
                return;
            }

            if (!datos.encontrado) {
                codigoActual = '';
                pintar({ clase: 'espera', estado: 'No encontrado', mensaje: datos.mensaje });
                return;
            }

            const socio = datos.socio;
            codigoActual = socio.codigo;
            pintar({
                clase: socio.estado === 'vigente' ? 'vigente' : 'vencida',
                estado: TITULOS[socio.estado] || 'Estado desconocido',
                nombre: socio.nombre,
                plan: socio.plan || '—',
                vence: socio.fecha_fin || '—',
                dias: textoDias(socio),
                mensaje: datos.mensaje,
                registrable: datos.puede_registrar,
            });
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            pintar({ clase: 'espera', estado: 'Sin respuesta', mensaje: 'No se pudo conectar con el servidor.' });
        }
    }

    function agregarFila(asistencia) {
        const fila = document.createElement('tr');
        [asistencia.hora, asistencia.codigo, asistencia.nombre].forEach((texto) => {
            const celda = document.createElement('td');
            celda.textContent = texto; // textContent evita XSS al insertar datos del servidor
            fila.appendChild(celda);
        });
        lista.insertBefore(fila, lista.firstChild);
        filaVacia.hidden = true;
        total.textContent = String(Number(total.textContent) + 1);
    }

    async function registrar() {
        boton.disabled = true;

        try {
            const respuesta = await fetch(campo.dataset.urlRegistrar, {
                method: 'POST',
                headers: { 'X-Requested-With': 'fetch', 'X-CSRF-Token': csrf, 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: new URLSearchParams({ codigo: codigoActual }),
            });
            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                ui.mensaje.textContent = datos.mensaje || 'No se pudo registrar la asistencia.';
                return;
            }

            agregarFila(datos.asistencia);
            ui.mensaje.textContent = `Asistencia registrada a las ${datos.asistencia.hora}.`;
            campo.select(); // listo para el siguiente socio
        } catch (error) {
            ui.mensaje.textContent = 'No se pudo conectar con el servidor.';
        }
    }

    campo.addEventListener('input', () => {
        clearTimeout(temporizador);
        const valor = campo.value.trim();

        if (valor === '') {
            pintarEspera();
            return;
        }
        temporizador = setTimeout(() => consultar(valor), 250);
    });

    campo.addEventListener('keydown', (evento) => {
        if (evento.key === 'Enter' && !boton.disabled) {
            evento.preventDefault();
            registrar();
        }
    });

    boton.addEventListener('click', registrar);
})();