/**
 * fo_vehicle.js — DoVehicle Front-Office
 *
 * Gère :
 * 1. Les selects en cascade Marque > Modèle > Motorisation (FO)
 * 2. La mémorisation du véhicule via cookie (appel AJAX POST)
 * 3. L'effacement du véhicule mémorisé
 * 4. La redirection vers la page produits compatibles
 */

(function () {
    'use strict';

    /* ── Config ──────────────────────────────────────────────────── */

    const FO = window.DOVEHICLE_FO || { ajax_url: '', selected: {} };

    /* ── Init ────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('dov-fo-form');

        if (!form) return;

        bindFoEvents(form);

        // Pré-charger les selects si un véhicule est déjà mémorisé
        if (FO.selected.id_manufacturer > 0) {
            preloadSelects(FO.selected);
        }
    });

    /* ── Événements ─────────────────────────────────────────────── */

    function bindFoEvents(form) {

        /* Changement marque → charger modèles */
        on('change', '#dov-fo-brand', function () {
            const idBrand = intVal(this.value);
            resetSelect('dov-fo-model',  true,  '— Modèle… —');
            resetSelect('dov-fo-engine', true,  '— Motorisation… —');
            toggleSearchBtn();

            if (!idBrand) return;
            fetchModels(idBrand);
        });

        /* Changement modèle → charger motorisations */
        on('change', '#dov-fo-model', function () {
            const idModel = intVal(this.value);
            resetSelect('dov-fo-engine', true, '— Motorisation… —');
            toggleSearchBtn();

            if (!idModel) return;
            fetchEngines(idModel);
        });

        /* Changement motorisation → activer bouton */
        on('change', '#dov-fo-engine', function () {
            toggleSearchBtn();
        });

        /* Soumission du formulaire → mémoriser + rediriger */
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            saveAndRedirect();
        });

        /* Bouton "Changer de véhicule" → afficher le form */
        on('click', '.dov-change-vehicle', function (e) {
            e.preventDefault();
            const wrapper = document.querySelector('.dovehicle-active-vehicle');
            const formEl  = document.getElementById('dov-fo-form');
            if (wrapper) wrapper.style.display = 'none';
            if (formEl)  formEl.classList.remove('dov-hidden');
        });

        /* Bouton "Effacer" → vider le cookie */
        on('click', '#dov-fo-clear', function (e) {
            e.preventDefault();
            clearVehicle();
        });
    }

    /* ── AJAX FO ─────────────────────────────────────────────────── */

    function fetchModels(idBrand) {
        ajaxGet(FO.ajax_url + '?action=getModels&id_manufacturer=' + idBrand, function (resp) {
            const select = document.getElementById('dov-fo-model');

            if (!resp.success || !resp.data.length) {
                select.innerHTML = '<option value="">— Aucun modèle —</option>';
                select.disabled  = true;
                return;
            }

            let html = '<option value="">— Choisir un modèle —</option>';
            resp.data.forEach(function (m) {
                let label = m.name;
                if (m.year_start) {
                    label += ' (' + m.year_start + (m.year_end ? '–' + m.year_end : '') + ')';
                }
                html += '<option value="' + m.id + '">' + escHtml(label) + '</option>';
            });

            select.innerHTML = html;
            select.disabled  = false;
            toggleSearchBtn();
        });
    }

    function fetchEngines(idModel) {
        ajaxGet(FO.ajax_url + '?action=getEngines&id_model=' + idModel, function (resp) {
            const select = document.getElementById('dov-fo-engine');

            if (!resp.success || !resp.data.length) {
                select.innerHTML = '<option value="">— Aucune motorisation —</option>';
                select.disabled  = false;  // OK de chercher sans motorisation précise
                toggleSearchBtn();
                return;
            }

            let html = '<option value="">— Toutes motorisations —</option>';
            resp.data.forEach(function (e) {
                html += '<option value="' + e.id + '">' + escHtml(e.label) + '</option>';
            });

            select.innerHTML = html;
            select.disabled  = false;
            toggleSearchBtn();
        });
    }

    /**
     * Mémorise le véhicule via AJAX POST, puis redirige vers la page produits
     */
    function saveAndRedirect() {
        const idBrand  = intVal(getVal('dov-fo-brand'));
        const idModel  = intVal(getVal('dov-fo-model'));
        const idEngine = intVal(getVal('dov-fo-engine'));

        if (!idBrand) return;

        const formData = new URLSearchParams();
        formData.append('action',          'saveVehicle');
        formData.append('id_manufacturer', idBrand);
        formData.append('id_model',        idModel);
        formData.append('id_engine',       idEngine);

        fetch(FO.ajax_url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    formData.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (resp) {
            if (resp.success && resp.redirect) {
                window.location.href = resp.redirect;
            }
        })
        .catch(function (err) {
            console.error('[DoVehicle] saveAndRedirect error:', err);
        });
    }

    /**
     * Efface le véhicule mémorisé et recharge la page
     */
    function clearVehicle() {
        const formData = new URLSearchParams();
        formData.append('action', 'clearVehicle');

        fetch(FO.ajax_url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    formData.toString()
        })
        .then(function () {
            window.location.reload();
        });
    }

    /**
     * Pré-charge les selects modèle + motorisation si un véhicule est déjà en cookie
     */
    function preloadSelects(selected) {
        if (!selected.id_manufacturer) return;

        fetchModels(selected.id_manufacturer);

        // Attendre que les modèles soient chargés puis sélectionner le bon
        // On utilise un MutationObserver sur le select modèle
        if (selected.id_model > 0) {
            waitAndSelect('dov-fo-model', selected.id_model, function () {
                fetchEngines(selected.id_model);

                if (selected.id_engine > 0) {
                    waitAndSelect('dov-fo-engine', selected.id_engine, function () {
                        toggleSearchBtn();
                    });
                }
            });
        }
    }

    /**
     * Attend que le select soit peuplé, puis sélectionne la valeur demandée
     */
    function waitAndSelect(selectId, value, callback) {
        const select  = document.getElementById(selectId);
        let attempts  = 0;
        const maxWait = 30; // 3 secondes max

        const interval = setInterval(function () {
            attempts++;

            if (select.options.length > 1) {
                // Les options sont chargées
                clearInterval(interval);
                select.value = value;

                if (typeof callback === 'function') {
                    // Déclencher l'event change pour propager
                    select.dispatchEvent(new Event('change'));
                    callback();
                }
            }

            if (attempts >= maxWait) {
                clearInterval(interval);
            }
        }, 100);
    }

    /* ── Helpers UI ─────────────────────────────────────────────── */

    function toggleSearchBtn() {
        const btn    = document.getElementById('dov-fo-search');
        const brand  = intVal(getVal('dov-fo-brand'));
        if (btn) btn.disabled = !brand;
    }

    function resetSelect(id, disabled, placeholder) {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = '<option value="">' + escHtml(placeholder || '—') + '</option>';
        el.disabled  = !!disabled;
    }

    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    function intVal(v) {
        return parseInt(v, 10) || 0;
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }

    /* ── Helpers réseau ─────────────────────────────────────────── */

    function on(event, selector, handler) {
        document.addEventListener(event, function (e) {
            const el = e.target.closest(selector);
            if (el) handler.call(el, e);
        });
    }

    function ajaxGet(url, callback) {
        fetch(url, { method: 'GET' })
            .then(function (r) { return r.json(); })
            .then(callback)
            .catch(function (err) { console.error('[DoVehicle] AJAX error:', err); });
    }

})();
