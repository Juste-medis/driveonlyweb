$(document).ready(() => {
    const originalEmit = prestashop.emit;
    const FO = window.DOVEHICLE_FO || { ajax_url: '', selected: {} };

    prestashop.emit = function (eventName, data) {
        console.log('EVENT:', eventName, data);
        return originalEmit.apply(this, arguments);
    };
    prestashop.on('updateProductList', function (event) {
        let totalItems = event.pagination.total_items

        console.log('updateProductList event received:', event);
        let actalhtml = $('.category-product-count, .heading-counter').html();
        // remplace le nombre total de produits dans le texte
        if (actalhtml) {
            const newHtml = actalhtml.replace(/\d+/, totalItems);
            $('.category-product-count, .heading-counter').html(newHtml);
        }

    });

    const resetButton = document.querySelector('.dovehicle-reset-filters');

    const filtersContainer = document.getElementById('dovehicle_filters');
    if (!filtersContainer) return;

    // ─── CHECKBOXES ──────────────────────────────────────────────────────────
    const attributeCheckboxes = document.querySelectorAll('.dovehicle-filter-attr');
    const featureCheckboxes = document.querySelectorAll('.dovehicle-filter-feature');

    // ─── SELECTS VÉHICULE ────────────────────────────────────────────────────
    const selManufacturer = document.getElementById('dov-filter-manufacturer');
    const selModel = document.getElementById('dov-filter-model');
    const selEngine = document.getElementById('dov-filter-engine');
    const selFamily = document.getElementById('dov-filter-family');

    // Snapshot de toutes les options pour le filtrage en cascade
    const allModelOptions = selModel ? Array.from(selModel.querySelectorAll('option[data-manufacturer]')) : [];
    const allEngineOptions = selEngine ? Array.from(selEngine.querySelectorAll('option[data-model]')) : [];

    // ─── INIT depuis URL ─────────────────────────────────────────────────────
    initFromUrl();
    updateResetButtonVisibility();

    // ─── LISTENERS CHECKBOXES ────────────────────────────────────────────────
    attributeCheckboxes.forEach(cb => cb.addEventListener('change', applyAllFilters));
    featureCheckboxes.forEach(cb => cb.addEventListener('change', applyAllFilters));

    // ─── LISTENERS SELECTS ───────────────────────────────────────────────────
    if (selManufacturer) {
        selManufacturer.addEventListener('change', function () {
            const idManufacturer = this.value;

            filterOptions(selModel, allModelOptions, 'manufacturer', idManufacturer);
            selModel.disabled = !idManufacturer;

            filterOptions(selEngine, allEngineOptions, 'model', '');
            selEngine.disabled = true;

            applyAllFilters();
        });
    }

    if (selModel) {
        selModel.addEventListener('change', function () {
            const idModel = this.value;

            filterOptions(selEngine, allEngineOptions, 'model', idModel);
            selEngine.disabled = !idModel;

            applyAllFilters();
        });
    }

    if (selEngine) selEngine.addEventListener('change', applyAllFilters);
    if (selFamily) selFamily.addEventListener('change', applyAllFilters);

    // ─── RESET ───────────────────────────────────────────────────────────────
    if (resetButton) {
        resetButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Reset checkboxes
            attributeCheckboxes.forEach(el => el.checked = false);
            featureCheckboxes.forEach(el => el.checked = false);


            // Reset selects
            if (selManufacturer) selManufacturer.value = '';
            if (selModel) { filterOptions(selModel, allModelOptions, 'manufacturer', ''); selModel.disabled = true; }
            if (selEngine) { filterOptions(selEngine, allEngineOptions, 'model', ''); selEngine.disabled = true; }
            if (selFamily) selFamily.value = '';

            const url = new URL(window.location);
            ['attributes[]', 'features[]', 'dov_manufacturer', 'dov_model', 'dov_engine', 'dov_family']
                .forEach(k => url.searchParams.delete(k));


            //page =1 à chaque changement de filtre
            url.searchParams.set('page', '1');

            updateResetButtonVisibility();
            applyAllFilters();
        });
    }

    // ─── FONCTIONS ───────────────────────────────────────────────────────────

    /**
     * Collecte tous les filtres actifs (checkboxes + selects) et émet updateFacets
     */
    function applyAllFilters() {
        const url = new URL(window.location);

        // Nettoyer tous les params gérés par ce module
        [...url.searchParams.keys()].forEach(key => {
            if (
                key.startsWith('attributes') ||
                key.startsWith('features') ||
                key.startsWith('dov_')
            ) {
                url.searchParams.delete(key);
            }
        });

        //page =1 à chaque changement de filtre
        url.searchParams.set('page', '1');

        // Checkboxes attributs
        Array.from(attributeCheckboxes)
            .filter(el => el.checked)
            .forEach(el => url.searchParams.append('attributes[]', el.value));

        // Checkboxes features
        Array.from(featureCheckboxes)
            .filter(el => el.checked)
            .forEach(el => url.searchParams.append('features[]', el.value));

        // Selects véhicule
        if (selManufacturer && selManufacturer.value) url.searchParams.set('dov_manufacturer', selManufacturer.value);
        if (selModel && selModel.value) url.searchParams.set('dov_model', selModel.value);
        if (selEngine && selEngine.value) url.searchParams.set('dov_engine', selEngine.value);
        if (selFamily && selFamily.value) url.searchParams.set('dov_family', selFamily.value);

        window.history.replaceState({}, '', url.toString());
        prestashop.emit('updateFacets', url.toString());
        updateResetButtonVisibility();

        let data = {
            attributes: Array.from(attributeCheckboxes).filter(el => el.checked).map(el => el.value),
            features: Array.from(featureCheckboxes).filter(el => el.checked).map(el => el.value),
            manufacturer: selManufacturer ? selManufacturer.value : '',
            model: selModel ? selModel.value : '',
            engine: selEngine ? selEngine.value : '',
            family: selFamily ? selFamily.value : ''
        };

        getnewFiltersAjax(data, function (resp) {
            // Traitez la réponse du serveur ici, par exemple en mettant à jour l'interface utilisateur
            console.log('Réponse du serveur:', resp);
        });
    }
    function getnewFiltersAjax(data, callback) {
        $.ajax({
            url: FO.ajax_url,
            type: 'GET',
            dataType: 'json',
            // data: {
            //     action: 'get_filters',
            //     id_product: FO.id_product,
            //     data: JSON.stringify(data),
            // },
            success: function (resp) {
                if (resp.success) {
                    callback(resp);
                } else {
                    alert('Erreur lors de l\'ajout.');
                }
            },
            error: function () {
                // En cas d'erreur réseau : supprimer quand même côté UI
                // La re-synchro à la sauvegarde corrigera l'état
                callback();
            }
        });
    }
    /**
     * Reconstruit un select en ne gardant que les options dont data-{dataAttr} === value
     * L'option vide (placeholder) est toujours conservée en premier
     */
    function filterOptions(selectEl, allOptions, dataAttr, value) {
        if (!selectEl) return;

        const placeholder = selectEl.querySelector('option[value=""]');
        selectEl.innerHTML = '';
        if (placeholder) selectEl.appendChild(placeholder.cloneNode(true));
        selectEl.value = '';

        allOptions.forEach(opt => {
            if (!value || opt.dataset[dataAttr] === value) {
                selectEl.appendChild(opt.cloneNode(true));
            }
        });
    }

    /**
     * Lit l'URL et restaure l'état complet de tous les filtres
     */
    function initFromUrl() {
        const url = new URL(window.location);

        // ── Checkboxes ──────────────────────────────────────────────────────
        for (const [key, value] of url.searchParams.entries()) {
            if (key.startsWith('attributes[')) {
                const cb = document.querySelector(`.dovehicle-filter-attr[value="${value}"]`);
                if (cb) cb.checked = true;
            }
            if (key.startsWith('features[')) {
                const cb = document.querySelector(`.dovehicle-filter-feature[value="${value}"]`);
                if (cb) cb.checked = true;
            }
        }

        // ── Selects véhicule ────────────────────────────────────────────────
        const idManufacturer = url.searchParams.get('dov_manufacturer') || '';
        const idModel = url.searchParams.get('dov_model') || '';
        const idEngine = url.searchParams.get('dov_engine') || '';
        const idFamily = url.searchParams.get('dov_family') || '';

        if (selManufacturer && idManufacturer) {
            selManufacturer.value = idManufacturer;

            filterOptions(selModel, allModelOptions, 'manufacturer', idManufacturer);
            if (selModel) selModel.disabled = false;

            if (idModel) {
                selModel.value = idModel;

                filterOptions(selEngine, allEngineOptions, 'model', idModel);
                if (selEngine) selEngine.disabled = false;

                if (idEngine) selEngine.value = idEngine;
            }
        }

        if (selFamily && idFamily) selFamily.value = idFamily;
    }

    /**
     * Met à jour la visibilité du bouton reset selon l'état des filtres
     */
    function updateResetButtonVisibility() {

        const hasActiveFilter =
            Array.from(attributeCheckboxes).some(el => el.checked) ||
            Array.from(featureCheckboxes).some(el => el.checked) ||
            (selManufacturer && selManufacturer.value) ||
            (selModel && selModel.value) ||
            (selEngine && selEngine.value) ||
            (selFamily && selFamily.value);



        if (resetButton) {
            const show = (!!hasActiveFilter) ? 'block' : 'none';
            resetButton.style.display = show;
        }
    }

});