/**
 * product_vehicle_tab.js
 * Script pour injecter et gérer le tab "Compatibilité Véhicule" dans la fiche produit BO
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('[DoVehicle] product_vehicle_tab.js loaded');

    // Récupérer le contenu du template stocké dans le champ caché
    const templateField = document.getElementById('dovehicle_template_html');

    if (!templateField) {
        console.warn('[DoVehicle] dovehicle_template_html field not found');
        return;
    }

    // Récupérer le contenu HTML du template depuis la valeur du champ
    const templateContent = templateField.value;

    if (!templateContent || templateContent.trim().length === 0) {
        console.warn('[DoVehicle] No template content found');
        return;
    }

    console.log('[DoVehicle] Template content loaded, length: ' + templateContent.length);

    // Créer un conteneur pour injecter le template
    const container = document.createElement('div');
    container.id = 'dovehicle-container';
    container.innerHTML = templateContent;

    // Injecter le conteneur dans la page
    // Essayer plusieurs emplacements possibles
    let injected = false;

    // Option 1: Ajouter après le dernier champ du formulaire produit
    const lastFormGroup = Array.from(document.querySelectorAll('.form-section')).pop();
    if (lastFormGroup) {
        lastFormGroup.appendChild(container);
        injected = true;
        console.log('[DoVehicle] Template injected after form-section');
    }

    // Option 2: Ou dans le formulaire principal
    if (!injected) {
        const mainForm = document.querySelector('form');
        if (mainForm) {
            mainForm.appendChild(container);
            injected = true;
            console.log('[DoVehicle] Template injected into main form');
        }
    }

    if (injected) {
        // Initialiser les fonctionalités du tab DoVehicle
        initProductVehicleTab();
    } else {
        console.error('[DoVehicle] Could not find a suitable location to inject template');
    }
});

/**
 * Initialise les fonctionalités du tab DoVehicle
 */
function initProductVehicleTab() {
    const brandSelect = document.getElementById('dov-select-brand');
    const modelSelect = document.getElementById('dov-select-model');
    const engineSelect = document.getElementById('dov-select-engine');
    const addButton = document.getElementById('dov-btn-add-compat');

    if (!brandSelect) {
        console.warn('[DoVehicle] Brand select not found - aborting initialization');
        return;
    }

    console.log('[DoVehicle] Initializing...');

    // Récupérer l'URL AJAX et le token depuis les variables JavaScript définies par PrestaShop
    // Ces variables sont créées par Media::addJsDefL() dans le hook actionAdminControllerSetMedia
    const ajaxUrl = (typeof dovehicleAjaxUrl !== 'undefined') ? dovehicleAjaxUrl : '/admin/?controller=AdminDoVehicleAjax';
    const token = (typeof dovehicleToken !== 'undefined') ? dovehicleToken : '';

    console.log('[DoVehicle] AJAX URL: ' + ajaxUrl);
    console.log('[DoVehicle] Token: ' + (token ? 'OK' : 'MISSING'));

    // Event: Change de marque → Charger les modèles
    brandSelect.addEventListener('change', async function () {
        const idBrand = parseInt(this.value) || 0;
        console.log('[DoVehicle] Brand selected: ' + idBrand);

        if (!idBrand) {
            modelSelect.innerHTML = '<option value="">— Choisir un modèle —</option>';
            modelSelect.disabled = true;
            engineSelect.innerHTML = '<option value="">— Choisir une motorisation —</option>';
            engineSelect.disabled = true;
            addButton.disabled = true;
            return;
        }

        try {
            const url = ajaxUrl + '&action=getModels&id_manufacturer=' + idBrand + '&token=' + token;
            console.log('[DoVehicle] Fetching: ' + url);

            const response = await fetch(url);
            const data = await response.json();

            console.log('[DoVehicle] Models response:', data);

            if (data.success && data.data) {
                modelSelect.innerHTML = '<option value="">— Choisir un modèle —</option>';
                data.data.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id_do_vehicle_model;
                    option.textContent = model.name + (model.year_start ? ` (${model.year_start}-${model.year_end})` : '');
                    modelSelect.appendChild(option);
                });
                modelSelect.disabled = false;
            } else {
                console.warn('[DoVehicle] No models returned:', data);
            }
        } catch (error) {
            console.error('[DoVehicle] Error loading models:', error);
        }

        // Reset motorisations
        engineSelect.innerHTML = '<option value="">— Choisir une motorisation —</option>';
        engineSelect.disabled = true;
    });

    // Event: Change de modèle → Charger les motorisations
    modelSelect.addEventListener('change', async function () {
        const idModel = parseInt(this.value) || 0;
        console.log('[DoVehicle] Model selected: ' + idModel);

        if (!idModel) {
            engineSelect.innerHTML = '<option value="">— Choisir une motorisation —</option>';
            engineSelect.disabled = true;
            addButton.disabled = true;
            return;
        }

        try {
            const url = ajaxUrl + '&action=getEngines&id_model=' + idModel + '&token=' + token;
            const response = await fetch(url);
            const data = await response.json();

            console.log('[DoVehicle] Engines response:', data);

            if (data.success && data.data) {
                engineSelect.innerHTML = '<option value="">— Choisir une motorisation —</option>';
                data.data.forEach(engine => {
                    const option = document.createElement('option');
                    option.value = engine.id_do_vehicle_engine;
                    option.textContent = engine.name + (engine.power_hp ? ` (${engine.power_hp}ch)` : '');
                    engineSelect.appendChild(option);
                });
                engineSelect.disabled = false;
            }
        } catch (error) {
            console.error('[DoVehicle] Error loading engines:', error);
        }
    });

    // Event: Sélection motorisation → Activer le bouton Ajouter
    engineSelect.addEventListener('change', function () {
        addButton.disabled = !this.value;
    });

    // Event: Clic sur Ajouter compatibilité
    addButton.addEventListener('click', function () {
        const idBrand = parseInt(brandSelect.value) || 0;
        const idModel = parseInt(modelSelect.value) || 0;
        const idEngine = parseInt(engineSelect.value) || 0;
        const noteField = document.getElementById('dov-input-note');
        const note = noteField ? noteField.value : '';

        if (!idBrand || !idModel || !idEngine) {
            alert('Veuillez sélectionner tous les champs');
            return;
        }

        console.log('[DoVehicle] Adding compatibility: ' + idBrand + ' / ' + idModel + ' / ' + idEngine);

        addCompatibilityLine(idBrand, idModel, idEngine, note);

        // Reset les sélections
        brandSelect.selectedIndex = 0;
        modelSelect.selectedIndex = 0;
        modelSelect.disabled = true;
        engineSelect.selectedIndex = 0;
        engineSelect.disabled = true;
        addButton.disabled = true;
        if (noteField) noteField.value = '';
    });

    // Charger les compatibilités existantes depuis le champ JSON
    loadExistingCompatibilities();

    console.log('[DoVehicle] Initialization complete');
}

/**
 * Charge les compatibilités existantes depuis le champ JSON caché
 */
function loadExistingCompatibilities() {
    const jsonField = document.getElementById('dovehicle_compat_json');
    if (!jsonField) return;

    try {
        const compats = JSON.parse(jsonField.value || '[]');
        const tbody = document.querySelector('#dovehicle-compat-table tbody');

        if (tbody && compats.length > 0) {
            // Clear existing rows
            tbody.innerHTML = '';

            compats.forEach(compat => {
                addCompatibilityLine(
                    compat.id_manufacturer,
                    compat.id_do_vehicle_model,
                    compat.id_do_vehicle_engine || 0,
                    compat.note || '',
                    compat.id_do_product_vehicle_compat || 0
                );
            });
        }

        console.log('[DoVehicle] Loaded ' + compats.length + ' existing compatibilities');
    } catch (error) {
        console.error('[DoVehicle] Error loading compatibilities:', error);
    }
}

/**
 * Ajoute une ligne de compatibilité dans le tableau
 */
function addCompatibilityLine(idBrand, idModel, idEngine, note = '', compatId = 0) {
    const tbody = document.querySelector('#dovehicle-compat-table tbody');
    if (!tbody) {
        console.warn('[DoVehicle] Table tbody not found');
        return;
    }

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${idBrand}</td>
        <td>${idModel}</td>
        <td>${idEngine}</td>
        <td>${note}</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm dov-delete-btn">
                <i class="material-icons">delete</i>
            </button>
        </td>
    `;

    // Auto-supprimer en cliquant le bouton delete
    row.querySelector('.dov-delete-btn').addEventListener('click', function (e) {
        e.preventDefault();
        row.remove();
        updateJsonField();
    });

    tbody.appendChild(row);
    updateJsonField();
}

/**
 * Met à jour le champ JSON caché avec les données du tableau
 */
function updateJsonField() {
    const tbody = document.querySelector('#dovehicle-compat-table tbody');
    const jsonField = document.getElementById('dovehicle_compat_json');

    if (!tbody || !jsonField) return;

    const compats = [];
    tbody.querySelectorAll('tr').forEach((row, idx) => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
            compats.push({
                id_compat: 0,
                id_manufacturer: parseInt(cells[0].textContent) || 0,
                id_do_vehicle_model: parseInt(cells[1].textContent) || 0,
                id_do_vehicle_engine: parseInt(cells[2].textContent) || 0,
                note: cells[3].textContent || '',
            });
        }
    });

    jsonField.value = JSON.stringify(compats);
    console.log('[DoVehicle] Updated JSON field with ' + compats.length + ' compatibilities');
}
