{*
 * Template — Bloc "Compatibilité Véhicule" dans la fiche produit BO
 * Injecté via hookActionProductFormBuilderModifier
 * Variables assignées dans dovehicle.php::hookActionProductFormBuilderModifier
 *}

<div class="card mt-3" id="dovehicle-card">
  <div class="card-header">
    <h3 class="card-header-title">
      <i class="material-icons">directions_car</i>
      {l s='Compatibilité Véhicule' mod='dovehicle'}
    </h3>
  </div>

  <div class="card-body">

    {* ─── Sélecteurs en cascade ───────────────────────────────── *}
    <div class="row">

      {* Marque *}
      <div class="col-md-3">
        <label class="form-control-label">{l s='Marque' mod='dovehicle'}</label>
        <select id="dov-select-brand" class="form-control custom-select">
          <option value="">{l s='— Choisir une marque —' mod='dovehicle'}</option>
          {foreach $dovehicle_manufacturers as $brand}
            <option value="{$brand.id_manufacturer|intval}">{$brand.name|escape:'html'}</option>
          {/foreach}
        </select>
      </div>

      {* Modèle (chargé en AJAX) *}
      <div class="col-md-3">
        <label class="form-control-label">{l s='Modèle' mod='dovehicle'}</label>
        <select id="dov-select-model" class="form-control custom-select" disabled>
          <option value="">{l s='— Choisir un modèle —' mod='dovehicle'}</option>
        </select>
      </div>

      {* Motorisation (chargée en AJAX) *}
      <div class="col-md-4">
        <label class="form-control-label">{l s='Motorisation' mod='dovehicle'}</label>
        <select id="dov-select-engine" class="form-control custom-select" disabled>
          <option value="">{l s='— Choisir une motorisation —' mod='dovehicle'}</option>
        </select>
      </div>

      {* Note optionnelle *}
      <div class="col-md-2 d-flex align-items-end flex-column justify-content-end">
         <button type="button" id="dov-btn-add-compat" class="btn btn-primary" disabled>
          <i class="material-icons">add</i>
          {l s='Ajouter' mod='dovehicle'}
        </button>
      </div>

    </div>{* /row selectors *}

    {* Note optionnelle sur la compatibilité *}
    <div class="row mt-2" id="dov-note-row" style="display:none!important">
      <div class="col-md-10">
        <input type="text"
               id="dov-input-note"
               class="form-control"
               placeholder="{l s='Note optionnelle (ex : coupé uniquement)' mod='dovehicle'}"
               maxlength="255">
      </div>
    </div>

    {* ─── Tableau des compatibilités existantes ───────────────── *}
    <div class="mt-3">
      <table class="table table-sm table-bordered" id="dov-compat-table">
        <thead class="thead-light">
          <tr>
            <th>{l s='Marque' mod='dovehicle'}</th>
            <th>{l s='Modèle' mod='dovehicle'}</th>
            <th>{l s='Motorisation' mod='dovehicle'}</th>
            <th>{l s='Note' mod='dovehicle'}</th>
            <th style="width:50px">{l s='Suppr.' mod='dovehicle'}</th>
          </tr>
        </thead>
        <tbody id="dov-compat-tbody">

          {if $dovehicle_existing_compats|count > 0}
            {foreach $dovehicle_existing_compats as $compat}
              <tr data-id="{$compat.id_compat|intval}">
                <td>{$compat.manufacturer_name|default:'—'|escape:'html'}</td>
                <td>{$compat.model_name|default:'—'|escape:'html'}</td>
                <td>
                  {if $compat.engine_name}
                    {$compat.engine_name|escape:'html'}
                    {if $compat.power_hp} ({$compat.power_hp|intval}ch){/if}
                  {else}—{/if}
                </td>
                <td>{$compat.note|default:''|escape:'html'}</td>
                <td class="text-center">
                  <button type="button"
                          class="btn btn-danger btn-xs dov-btn-delete"
                          data-id="{$compat.id_compat|intval}"
                          title="{l s='Supprimer' mod='dovehicle'}">
                    <i class="material-icons" style="font-size:16px">delete</i>
                  </button>
                </td>
              </tr>
            {/foreach}
          {else}
            <tr id="dov-empty-row">
              <td colspan="5" class="text-center text-muted">
                {l s='Aucune compatibilité définie' mod='dovehicle'}
              </td>
            </tr>
          {/if}

        </tbody>
      </table>
    </div>

    {* ─── Familles produit (multi-select) ────────────────────── *}
    <hr>
    <h4 class="mt-3">{l s='Famille(s) produit' mod='dovehicle'}</h4>

    <div class="row">
      {foreach $dovehicle_all_families as $family}
        <div class="col-md-3 mb-1">
          <div class="custom-control custom-checkbox">
            <input type="checkbox"
                   class="custom-control-input dov-family-checkbox"
                    id="dov-family-{$family.id_do_product_family|intval}"
                   value="{$family.id_do_product_family|intval}"
                   {if in_array($family.id_do_product_family, $dovehicle_linked_families)}checked{/if}>
            <label class="custom-control-label"
                   for="dov-family-{$family.id_do_product_family|intval}">
              {if $family.id_parent}
                <span class="text-muted">↳ </span>
              {/if}
              {$family.name|escape:'html'}
            </label>
          </div>
        </div>
      {/foreach}
    </div>

  </div>{* /card-body *}
</div>{* /card *}

{* Champs hidden injectés dans le formulaire — mis à jour par JS *}
<input type="hidden" id="dovehicle_compat_json"   name="product[dovehicle_compat_json]"   value="{$dovehicle_compat_json|escape:'html'}">
<input type="hidden" id="dovehicle_families_json" name="product[dovehicle_families_json]" value="{$dovehicle_families_json|escape:'html'}">

{* Configuration JS *}
<script>
      window.DOVEHICLE_CONFIG = {
          ajax_url: '{$dovehicle_ajax_url|escape:'javascript'}',
          token:    '{$dovehicle_token|escape:'javascript'}',
          id_product: {$dovehicle_id_product|intval},
          module_dir: '{$module_dir|escape:'javascript'}',
      };
</script>
<script src="{$module_dir}/views/js/bo_vehicle.js?v={time()}"></script>
 
