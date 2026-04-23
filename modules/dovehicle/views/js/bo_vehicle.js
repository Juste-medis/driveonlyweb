/**
 * bo_vehicle.js — DoVehicle Back-Office
 *
 * Gère :
 * 1. Les selects en cascade Marque > Modèle > Motorisation
 * 2. L'ajout/suppression de compatibilités dans le tableau
 * 3. La sérialisation en JSON dans les champs hidden avant soumission du form PS
 *
 * Dépendances : jQuery (disponible nativement dans le BO PS 1.7)
 */

(function ($) {
    'use strict';

    /* ── Config ─────────────────────────────────────────────────── */

    const CFG = window.DOVEHICLE_CONFIG || {};
    console.log('DOVEHICLE_CONFIGd', window.DOVEHICLE_CONFIG);

    /* Compatibilités en mémoire (tableau JS) */
    let compatList = [];

    /* ── Init ───────────────────────────────────────────────────── */

    $(document).ready(function () {

        // Lire l'état initial depuis les hidden fields
        try {
            const rawCompat = $('#dovehicle_compat_json').val();
            compatList = rawCompat ? JSON.parse(rawCompat) : [];
        } catch (e) {
            compatList = [];
        }

        // Rendu initial du tableau (si compatibilités préexistantes)
        if (compatList.length) {
            renderCompatTable(compatList);
        }

        bindEvents();
    });

    /* ── Événements ─────────────────────────────────────────────── */

    function bindEvents() {

        /* Changement de marque → charger modèles */
        $(document).on('change', '#dov-select-brand', function () {
            const idBrand = parseInt($(this).val(), 10);

            resetSelect('#dov-select-model', true);
            resetSelect('#dov-select-engine', true);
            toggleAddBtn();

            if (!idBrand) return;

            loadModels(idBrand);
        });

        /* Changement de modèle → charger motorisations */
        $(document).on('change', '#dov-select-model', function () {
            const idModel = parseInt($(this).val(), 10);

            resetSelect('#dov-select-engine', true);
            toggleAddBtn();

            if (!idModel) return;

            loadEngines(idModel);
        });

        /* Changement motorisation → activer le bouton Ajouter */
        $(document).on('change', '#dov-select-engine', function () {
            toggleAddBtn();
        });

        /* Clic "Ajouter compatibilité" */
        $(document).on('click', '#dov-btn-add-compat', function (e) {
            e.preventDefault();
            addCompat();
        });

        /* Suppression d'une ligne depuis le tableau */
        $(document).on('click', '.dov-btn-delete', function (e) {
            e.preventDefault();

            const $row = $(this).closest('tr');
            const idCompat = parseInt($row.data('id'), 10) || 0;
            const rowIndex = parseInt($row.data('index'), 10);

            if (idCompat > 0) {
                // Appel AJAX pour supprimer en base (si déjà sauvegardé)
                deleteCompatAjax(idCompat, function () {
                    $row.remove();
                    removeFromList(idCompat, rowIndex);
                    checkEmptyTable();
                });
            } else {
                // Ligne non encore sauvegardée → suppression locale seule
                $row.remove();
                removeFromList(idCompat, rowIndex);
                checkEmptyTable();
            }
        });

        /* Avant soumission du form PS → sérialiser les données dans les hiddens */
        $(document).on('submit', 'form', function () {
            serializeToHiddens();
        });

        /* PrestaShop 1.7 utilise aussi des boutons "Enregistrer" custom en dehors du form */
        $(document).on('click', '#product_footer button[type="submit"], .product-footer button[type="submit"]', function () {
            serializeToHiddens();
        });

        /* Familles : mise à jour du JSON hidden à chaque changement */
        $(document).on('change', '.dov-family-checkbox', function () {
            serializeFamiliesToHidden();
        });
    }

    /* ── Chargement AJAX ─────────────────────────────────────────── */

    function loadModels(idBrand) {
        const $select = $('#dov-select-model');

        $select.prop('disabled', true).html('<option value="">Chargement…</option>');

        $.ajax({
            url: CFG.ajax_url,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'getModels',
                id_manufacturer: idBrand,
                token: CFG.token
            },
            success: function (resp) {
                if (!resp.success || !resp.data.length) {
                    $select.html('<option value="">— Aucun modèle —</option>');
                    return;
                }

                let options = '<option value="">— Choisir un modèle —</option>';

                resp.data.forEach(function (m) {
                    let label = m.name;
                    if (m.year_start) {
                        label += ' (' + m.year_start + (m.year_end ? '–' + m.year_end : '') + ')';
                    }
                    options += '<option value="' + m.id + '">' + escHtml(label) + '</option>';
                });

                $select.html(options).prop('disabled', false);
            },
            error: function () {
                $select.html('<option value="">— Erreur chargement —</option>');
            }
        });
    }

    function loadEngines(idModel) {
        const $select = $('#dov-select-engine');

        $select.prop('disabled', true).html('<option value="">Chargement…</option>');

        $.ajax({
            url: CFG.ajax_url,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'getEngines',
                id_model: idModel,
                token: CFG.token
            },
            success: function (resp) {
                if (!resp.success || !resp.data.length) {
                    $select.html('<option value="">— Aucune motorisation —</option>');
                    $select.prop('disabled', false); // autoriser "toutes motorisations"
                    toggleAddBtn();
                    return;
                }

                let options = '<option value="">— Toutes motorisations —</option>';

                resp.data.forEach(function (e) {
                    options += '<option value="' + e.id + '">' + escHtml(e.label) + '</option>';
                });

                $select.html(options).prop('disabled', false);
                toggleAddBtn();
            },
            error: function () {
                $select.html('<option value="">— Erreur —</option>');
            }
        });
    }

    /* ── Gestion du tableau compatibilités ──────────────────────── */

    function addCompat() {
        const $brandSel = $('#dov-select-brand');
        const $modelSel = $('#dov-select-model');
        const $engineSel = $('#dov-select-engine');

        const idBrand = parseInt($brandSel.val(), 10) || 0;
        const idModel = parseInt($modelSel.val(), 10) || 0;
        const idEngine = parseInt($engineSel.val(), 10) || 0;
        const note = $.trim($('#dov-input-note').val());

        if (!idBrand) {
            alert('Veuillez choisir une marque.');
            return;
        }

        // Vérifier doublon local
        const isDuplicate = compatList.some(function (c) {
            return c.id_manufacturer === idBrand
                && c.id_do_vehicle_model === idModel
                && c.id_do_vehicle_engine === idEngine;
        });

        if (isDuplicate) {
            alert('Cette compatibilité est déjà dans la liste.');
            return;
        }

        const newItem = {
            id_compat: 0,    // 0 = non encore persisté
            id_manufacturer: idBrand,
            manufacturer_name: $brandSel.find('option:selected').text(),
            id_do_vehicle_model: idModel,
            model_name: idModel ? $modelSel.find('option:selected').text() : '—',
            id_do_vehicle_engine: idEngine,
            engine_name: idEngine ? $engineSel.find('option:selected').text() : '—',
            note: note,
        };

        compatList.push(newItem);
        appendCompatRow(newItem, compatList.length - 1);

        // Remettre les selects
        resetSelect('#dov-select-model', true);
        resetSelect('#dov-select-engine', true);
        $('#dov-select-brand').val('');
        $('#dov-input-note').val('');
        toggleAddBtn();

        // Supprimer la ligne "Aucune compatibilité"
        $('#dov-empty-row').remove();
    }

    function appendCompatRow(item, index) {
        const engineLabel = item.engine_name && item.engine_name !== '—'
            ? item.engine_name
            : '—';

        const $tr = $('<tr>')
            .attr('data-id', item.id_compat)
            .attr('data-index', index);

        $tr.append('<td>' + escHtml(item.manufacturer_name || '—') + '</td>');
        $tr.append('<td>' + escHtml(item.model_name || '—') + '</td>');
        $tr.append('<td>' + escHtml(engineLabel) + '</td>');
        $tr.append('<td>' + escHtml(item.note || '') + '</td>');
        $tr.append(
            '<td class="text-center">' +
            '<button type="button" class="btn btn-danger btn-xs dov-btn-delete" ' +
            'data-id="' + item.id_compat + '" data-index="' + index + '">' +
            '<i class="material-icons" style="font-size:16px">delete</i></button></td>'
        );

        $('#dov-compat-tbody').append($tr);
    }

    function renderCompatTable(list) {
        // Re-render depuis la liste JS (après reload page par ex.)
        // Le template Smarty gère déjà le rendu initial,
        // cette fonction est utile si on reconstruit dynamiquement
        list.forEach(function (item, index) {
            // Mettre à jour les data-index sur les lignes existantes
            $('#dov-compat-tbody tr[data-id="' + item.id_compat + '"]').attr('data-index', index);
        });
    }

    function removeFromList(idCompat, rowIndex) {
        if (idCompat > 0) {
            compatList = compatList.filter(function (c) { return c.id_compat !== idCompat; });
        } else {
            compatList.splice(rowIndex, 1);
        }
    }

    function deleteCompatAjax(idCompat, callback) {
        $.ajax({
            url: CFG.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'deleteCompat',
                id_compat: idCompat,
                token: CFG.token
            },
            success: function (resp) {
                if (resp.success) {
                    callback();
                } else {
                    alert('Erreur lors de la suppression.');
                }
            },
            error: function () {
                // En cas d'erreur réseau : supprimer quand même côté UI
                // La re-synchro à la sauvegarde corrigera l'état
                callback();
            }
        });
    }

    function checkEmptyTable() {
        if ($('#dov-compat-tbody tr').length === 0) {
            $('#dov-compat-tbody').html(
                '<tr id="dov-empty-row"><td colspan="5" class="text-center text-muted">' +
                'Aucune compatibilité définie</td></tr>'
            );
        }
    }

    /* ── Sérialisation vers les champs hidden ───────────────────── */

    function serializeToHiddens() {
        // Sérialiser les compatibilités
        const cleanList = compatList.map(function (item) {
            return {
                id_compat: item.id_compat || 0,
                id_manufacturer: item.id_manufacturer || 0,
                id_do_vehicle_model: item.id_do_vehicle_model || 0,
                id_do_vehicle_engine: item.id_do_vehicle_engine || 0,
                note: item.note || '',
            };
        });

        $('#dovehicle_compat_json').val(JSON.stringify(cleanList));

        serializeFamiliesToHidden();
    }

    function serializeFamiliesToHidden() {
        const familyIds = [];

        $('.dov-family-checkbox:checked').each(function () {
            familyIds.push(parseInt($(this).val(), 10));
        });

        $('#dovehicle_families_json').val(JSON.stringify(familyIds));
    }

    /* ── UI helpers ─────────────────────────────────────────────── */

    function toggleAddBtn() {
        const hasBrand = parseInt($('#dov-select-brand').val(), 10) > 0;
        $('#dov-btn-add-compat').prop('disabled', !hasBrand);

        // Afficher la zone note si une marque est choisie
        if (hasBrand) {
            $('#dov-note-row').css('display', '');
        } else {
            $('#dov-note-row').css('display', 'none');
        }
    }

    function resetSelect(selector, disable) {
        const defaultLabels = {
            '#dov-select-model': '— Choisir un modèle —',
            '#dov-select-engine': '— Choisir une motorisation —',
        };

        $(selector)
            .html('<option value="">' + (defaultLabels[selector] || '—') + '</option>')
            .prop('disabled', !!disable);
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

})(jQuery);
