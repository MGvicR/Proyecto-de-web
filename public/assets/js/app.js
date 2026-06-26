document.addEventListener('DOMContentLoaded', () => {
    const alcaldiaSelect = document.getElementById('alcaldia_id');
    const coloniaSelect = document.getElementById('colonia_id');

    if (!alcaldiaSelect || !coloniaSelect) {
        return;
    }

    const esFiltro = coloniaSelect.dataset.filtro === '1';

    function textoOpcionVacia() {
        return esFiltro ? 'Todas' : 'Selecciona colonia';
    }

    function textoSinAlcaldia() {
        return esFiltro ? 'Todas' : 'Selecciona alcaldía primero';
    }

    function textoSinColonias() {
        return esFiltro ? 'Sin colonias' : 'Sin colonias disponibles';
    }

    const initialAlcaldia = alcaldiaSelect.value;
    const initialColonia = coloniaSelect.value;

    function apiUrl(path) {
        const base = window.APP_BASE_URL || '';
        return `${base}${path.startsWith('/') ? path : `/${path}`}`;
    }

    function coloniasLocales(alcaldiaId) {
        if (!window.COLONIAS_POR_ALCALDIA) {
            return null;
        }

        return window.COLONIAS_POR_ALCALDIA[alcaldiaId]
            ?? window.COLONIAS_POR_ALCALDIA[String(alcaldiaId)]
            ?? null;
    }

    function renderColonias(colonias, selectedId = '') {
        coloniaSelect.innerHTML = `<option value="">${textoOpcionVacia()}</option>`;

        if (!colonias || colonias.length === 0) {
            coloniaSelect.innerHTML = `<option value="">${textoSinColonias()}</option>`;
            return;
        }

        colonias.forEach((colonia) => {
            const option = document.createElement('option');
            option.value = colonia.id;
            option.textContent = colonia.nombre;
            if (selectedId && String(colonia.id) === String(selectedId)) {
                option.selected = true;
            }
            coloniaSelect.appendChild(option);
        });
    }

    async function fetchColonias(alcaldiaId) {
        const response = await fetch(
            apiUrl(`/api/colonias.php?alcaldia_id=${encodeURIComponent(alcaldiaId)}`)
        );

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    async function cargarColonias(alcaldiaId, selectedId = '') {
        if (!alcaldiaId) {
            coloniaSelect.innerHTML = `<option value="">${textoSinAlcaldia()}</option>`;
            return;
        }

        coloniaSelect.innerHTML = '<option value="">Cargando...</option>';

        try {
            let colonias = coloniasLocales(alcaldiaId);
            if (!colonias || colonias.length === 0) {
                colonias = await fetchColonias(alcaldiaId);
            }
            renderColonias(colonias, selectedId);
        } catch (error) {
            coloniaSelect.innerHTML = '<option value="">Error al cargar colonias</option>';
        }
    }

    alcaldiaSelect.addEventListener('change', () => {
        const nuevaAlcaldia = alcaldiaSelect.value;
        const conservarColonia = nuevaAlcaldia === initialAlcaldia ? initialColonia : '';
        cargarColonias(nuevaAlcaldia, conservarColonia);
    });

    if (initialAlcaldia) {
        cargarColonias(initialAlcaldia, initialColonia);
    }
});

document.querySelectorAll('.js-solo-numeros').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '');
    });
});
