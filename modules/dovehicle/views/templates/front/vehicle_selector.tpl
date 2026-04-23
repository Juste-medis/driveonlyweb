{*
 * Template — Sélecteur véhicule Front-Office
 * Affiché dans le hook configuré (displayTop / displayHeader / displayFooter)
 *}

<div id="dovehicle-selector" class="dovehicle-selector-wrapper">
  <div class="container">

    {if $dovehicle_selected_vehicle}
      {* Véhicule mémorisé — afficher un bandeau "Mon véhicule" *}
      <div class="dovehicle-active-vehicle">
        <span class="dov-icon">🚗</span>
        <strong>{l s='Mon véhicule :' mod='dovehicle'}</strong>
        <span class="dov-vehicle-label">
          {$dovehicle_selected_vehicle.manufacturer_name|default:''|escape:'html'}
          {if $dovehicle_selected_vehicle.model_name}
            {$dovehicle_selected_vehicle.model_name|escape:'html'}
          {/if}
          {if $dovehicle_selected_vehicle.engine_name}
            — {$dovehicle_selected_vehicle.engine_name|escape:'html'}
          {/if}
        </span>
        <a href="#" class="dov-change-vehicle">{l s='Changer' mod='dovehicle'}</a>
        <a href="#" class="dov-clear-vehicle" id="dov-fo-clear">{l s='Effacer' mod='dovehicle'}</a>
      </div>
    {/if}

    {* Sélecteur Marque > Modèle > Motorisation *}
    <form class="dovehicle-form {if $dovehicle_selected_vehicle}dov-hidden{/if}"
          id="dov-fo-form"
          novalidate>

      <div class="dov-selects-row">

        <div class="dov-select-group">
          <label for="dov-fo-brand">{l s='Marque' mod='dovehicle'}</label>
          <select id="dov-fo-brand" name="id_manufacturer" class="dov-select" required>
            <option value="">{l s='Marque…' mod='dovehicle'}</option>
            {foreach $dovehicle_manufacturers as $brand}
              <option value="{$brand.id_manufacturer|intval}"
                      {if isset($dovehicle_selected_vehicle.id_manufacturer)
                          && $dovehicle_selected_vehicle.id_manufacturer == $brand.id_manufacturer}
                          selected
                      {/if}>
                {$brand.name|escape:'html'}
              </option>
            {/foreach}
          </select>
        </div>

        <div class="dov-select-group">
          <label for="dov-fo-model">{l s='Modèle' mod='dovehicle'}</label>
          <select id="dov-fo-model" name="id_model" class="dov-select" disabled required>
            <option value="">{l s='Modèle…' mod='dovehicle'}</option>
          </select>
        </div>

        <div class="dov-select-group">
          <label for="dov-fo-engine">{l s='Motorisation' mod='dovehicle'}</label>
          <select id="dov-fo-engine" name="id_engine" class="dov-select" disabled>
            <option value="">{l s='Motorisation…' mod='dovehicle'}</option>
          </select>
        </div>

        <div class="dov-select-group dov-btn-group">
          <label>&nbsp;</label>
          <button type="submit" class="btn btn-primary" id="dov-fo-search" disabled>
            {l s='Trouver mes pièces' mod='dovehicle'}
          </button>
        </div>

      </div>{* /dov-selects-row *}

    </form>

  </div>{* /container *}
</div>{* /dovehicle-selector *}

{* Config JS FO — transmise au script fo_vehicle.js *}
<script>
(function(){
  window.DOVEHICLE_FO = {
    ajax_url:    '{$dovehicle_ajax_url_fo|escape:'javascript'}',
    selected: {
      id_manufacturer: {$dovehicle_selected_vehicle.id_manufacturer|default:0|intval},
      id_model:        {$dovehicle_selected_vehicle.id_do_vehicle_model|default:0|intval},
      id_engine:       {$dovehicle_selected_vehicle.id_do_vehicle_engine|default:0|intval}
    }
  };
})();
</script>
