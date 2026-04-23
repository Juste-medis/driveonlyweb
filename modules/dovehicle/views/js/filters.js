// import $ from 'jquery';
// import prestashop from 'prestashop';
// import 'velocity-animate';

$(document).ready(() => {

    // Vérifier si les éléments du filtre existent
    const filtersContainer = document.getElementById('dovehicle_filters');
    if (!filtersContainer) {
        return;
    }

    const attributeCheckboxes = document.querySelectorAll('.dovehicle-filter-attr');
    const featureCheckboxes = document.querySelectorAll('.dovehicle-filter-feature');

    if (attributeCheckboxes.length === 0 && featureCheckboxes.length === 0) {
        return;
    }

    // Initialiser les checkboxes depuis l'URL (si filtre déjà appliqué)
    initializeCheckboxesFromUrl();

    // Ajouter les event listeners
    attributeCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', handleFilterChange);
    });

    featureCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', handleFilterChange);
    });

    /**
     * Récupère les filtres sélectionnés et met à jour l'URL
     */
    function handleFilterChange() {
        const selectedAttributes = Array.from(attributeCheckboxes)
            .filter(el => el.checked)
            .map(el => el.value);

        const selectedFeatures = Array.from(featureCheckboxes)
            .filter(el => el.checked)
            .map(el => el.value);

        // Construire la nouvelle URL avec les paramètres
        const url = new URL(window.location);

        // Nettoyer les anciens paramètres de filtre
        [...url.searchParams.keys()].forEach(key => {
            if (key.startsWith('attributes') || key.startsWith('features')) {
                url.searchParams.delete(key);
            }
        });


        // Ajouter les nouveaux paramètres
        selectedAttributes.forEach(attr => {
            url.searchParams.append('attributes[]', attr);
        });

        selectedFeatures.forEach(feature => {
            url.searchParams.append('features[]', feature);
        });

        // Mettre à jour l'URL dans la barre d'adresse (sans recharger)
        window.history.replaceState({}, '', url.toString());


        prestashop.emit('updateFacets', window.location.href);
    }

    /**
     * Initialise les checkboxes depuis l'URL s'il y a des filtres appliqués
     */
    function initializeCheckboxesFromUrl() {
        const url = new URL(window.location);

        const attributeParams = [];
        const featureParams = [];

        for (const [key, value] of url.searchParams.entries()) {
            if (key.startsWith('attributes[')) {
                attributeParams.push(value);
            }
            if (key.startsWith('features[')) {
                featureParams.push(value);
            }
        }

        attributeParams.forEach(attrId => {
            const checkbox = document.querySelector(`.dovehicle-filter-attr[value="${attrId}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        featureParams.forEach(featureId => {
            const checkbox = document.querySelector(`.dovehicle-filter-feature[value="${featureId}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }

    // Support pour le bouton "Réinitialiser les filtres" s'il existe
    const resetButton = document.querySelector('.dovehicle-reset-filters');
    if (resetButton) {
        resetButton.addEventListener('click', function (e) {
            e.preventDefault();

            attributeCheckboxes.forEach(el => el.checked = false);
            featureCheckboxes.forEach(el => el.checked = false);

            // Rediriger vers l'URL sans paramètres de filtre
            const url = new URL(window.location);
            url.searchParams.delete('attributes[]');
            url.searchParams.delete('features[]');
            window.location = url.toString();
        });
    }








}); 