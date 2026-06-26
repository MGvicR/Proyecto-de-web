document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('fotos-espacios-list');
    const recamarasSelect = document.getElementById('recamaras');
    const banosSelect = document.getElementById('banos');
    const cocinasSelect = document.getElementById('cocinas');
    const estacionamientoSelect = document.getElementById('tiene_estacionamiento');

    if (!list || !recamarasSelect || !banosSelect || !cocinasSelect || !estacionamientoSelect) {
        return;
    }

    const existentes = {};
    (window.FOTOS_EXISTENTES || []).forEach((foto) => {
        existentes[`${foto.tipo}-${foto.numero}`] = foto;
    });

    let ultimaConfiguracion = '';

    function crearSlot(tipo, numero, label, requerido) {
        const clave = `${tipo}-${numero}`;
        const existente = existentes[clave];
        const slot = document.createElement('div');
        slot.className = 'foto-espacio-slot';

        const titulo = document.createElement('label');
        titulo.className = 'foto-espacio-label';
        titulo.textContent = label;
        titulo.setAttribute('for', `foto-${tipo}-${numero}`);

        const input = document.createElement('input');
        input.type = 'file';
        input.id = `foto-${tipo}-${numero}`;
        input.name = `fotos[${tipo}][${numero}]`;
        input.accept = 'image/jpeg,image/png,image/webp';
        input.className = 'foto-espacio-input';
        input.addEventListener('change', () => {
            const archivo = input.files && input.files[0];
            const maxBytes = 5 * 1024 * 1024;

            if (!archivo) {
                return;
            }

            if (archivo.size > maxBytes) {
                // eslint-disable-next-line no-alert
                alert(`${label}: cada foto debe pesar máximo 5 MB.`);
                input.value = '';
            }
        });
        if (requerido && !existente) {
            input.required = true;
        }

        slot.appendChild(titulo);

        if (existente) {
            const preview = document.createElement('div');
            preview.className = 'foto-espacio-preview';
            preview.innerHTML = `
                <img src="${existente.url}" alt="${label}">
                <span>Foto actual — sube otra para reemplazar</span>
            `;
            slot.appendChild(preview);
        }

        slot.appendChild(input);
        list.appendChild(slot);
    }

    function obtenerConfiguracion() {
        const recamaras = Math.max(1, parseInt(recamarasSelect.value, 10) || 1);
        const banos = Math.max(1, parseInt(banosSelect.value, 10) || 1);
        const cocinas = Math.max(1, parseInt(cocinasSelect.value, 10) || 1);
        const estacionamiento = estacionamientoSelect.value === '1' ? 1 : 0;

        return `${recamaras}|${banos}|${cocinas}|${estacionamiento}`;
    }

    function renderFotosEspacios(forzar = false) {
        const configuracion = obtenerConfiguracion();

        if (!forzar && configuracion === ultimaConfiguracion) {
            return;
        }

        if (ultimaConfiguracion !== '' && configuracion !== ultimaConfiguracion) {
            // eslint-disable-next-line no-alert
            alert('Al cambiar recámaras, baños, cocinas o estacionamiento debes volver a seleccionar las fotos.');
        }

        ultimaConfiguracion = configuracion;
        list.innerHTML = '';

        const partes = configuracion.split('|');
        const recamaras = parseInt(partes[0], 10);
        const banos = parseInt(partes[1], 10);
        const cocinas = parseInt(partes[2], 10);
        const tieneEstacionamiento = partes[3] === '1';

        for (let i = 1; i <= recamaras; i += 1) {
            crearSlot('recamara', i, `Recámara ${i}`, true);
        }

        for (let i = 1; i <= banos; i += 1) {
            crearSlot('bano', i, `Baño ${i}`, true);
        }

        for (let i = 1; i <= cocinas; i += 1) {
            crearSlot('cocina', i, `Cocina ${i}`, true);
        }

        if (tieneEstacionamiento) {
            crearSlot('estacionamiento', 1, 'Estacionamiento', true);
        }
    }

    [recamarasSelect, banosSelect, cocinasSelect, estacionamientoSelect].forEach((select) => {
        select.addEventListener('change', () => renderFotosEspacios(true));
    });

    renderFotosEspacios(true);
});
