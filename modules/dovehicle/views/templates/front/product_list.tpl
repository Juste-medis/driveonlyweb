{*
 * Template — Liste produits compatibles avec le véhicule sélectionné
 * Contrôleur : DovehicleVehicleModuleFrontController
 *}
{extends file='page.tpl'}

{block name='head_seo'}
  <title>{$dovehicle_meta_title|escape:'html'}</title>
  {* Canonical pour éviter les duplicatas SEO *}
  <link rel="canonical" href="{$urls.current_url|escape:'html'}">
{/block}

{block name='content'}
<section id="dovehicle-product-list" class="container">

  {* Breadcrumb *}
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      {foreach $dovehicle_breadcrumb as $crumb}
        <li class="breadcrumb-item {if $crumb@last}active{/if}">
          {if $crumb.url && !$crumb@last}
            <a href="{$crumb.url|escape:'html'}">{$crumb.label|escape:'html'}</a>
          {else}
            {$crumb.label|escape:'html'}
          {/if}
        </li>
      {/foreach}
    </ol>
  </nav>

  <h1 class="dov-page-title">{$dovehicle_vehicle_label|default:{l s='Catalogue' mod='dovehicle'}|escape:'html'}</h1>
  <p class="text-muted">{l s='%d résultat(s)' sprintf=[$dovehicle_total] mod='dovehicle'}</p>

  <div class="row">

    {* ─── Filtre familles (sidebar) ─────────────────────────── *}
    <aside class="col-md-3 col-lg-2">
      <h5 class="dov-filter-title">{l s='Famille produit' mod='dovehicle'}</h5>
      <ul class="list-unstyled dov-family-filter">

        <li>
          <a href="{$urls.current_url|escape:'html'}?id_manufacturer={$dovehicle_id_manufacturer}&id_model={$dovehicle_id_model}&id_engine={$dovehicle_id_engine}"
             class="{if !$dovehicle_id_family}active{/if}">
            {l s='Toutes catégories' mod='dovehicle'}
          </a>
        </li>

        {foreach $dovehicle_family_tree as $family}
          <li>
            <a href="{$urls.current_url|escape:'html'}?id_manufacturer={$dovehicle_id_manufacturer}&id_model={$dovehicle_id_model}&id_engine={$dovehicle_id_engine}&id_family={$family.id_do_product_family|intval}"
               class="{if $dovehicle_id_family == $family.id_do_product_family}active{/if}">
              {$family.name|escape:'html'}
            </a>

            {if $family.children|count > 0}
              <ul class="dov-subfamily-list">
                {foreach $family.children as $child}
                  <li>
                    <a href="{$urls.current_url|escape:'html'}?id_manufacturer={$dovehicle_id_manufacturer}&id_model={$dovehicle_id_model}&id_engine={$dovehicle_id_engine}&id_family={$child.id_do_product_family|intval}"
                       class="{if $dovehicle_id_family == $child.id_do_product_family}active{/if}">
                      ↳ {$child.name|escape:'html'}
                    </a>
                  </li>
                {/foreach}
              </ul>
            {/if}
          </li>
        {/foreach}

      </ul>
    </aside>

    {* ─── Grille produits ────────────────────────────────────── *}
    <div class="col-md-9 col-lg-10">

      {if $dovehicle_products|count > 0}

        <div class="row">
          {foreach $dovehicle_products as $product}
            <div class="col-6 col-md-4 col-lg-3 mb-4">
              <article class="product-miniature dov-product-card">

                <a href="{$product.link|escape:'html'}" class="dov-product-img-link">
                  {if $product.cover_image}
                    <img src="{$product.cover_image|escape:'html'}"
                         alt="{$product.name|escape:'html'}"
                         loading="lazy"
                         class="img-fluid">
                  {else}
                    <div class="dov-no-img">{l s='Pas d\'image' mod='dovehicle'}</div>
                  {/if}
                </a>

                <div class="dov-product-info">
                  <h3 class="dov-product-name">
                    <a href="{$product.link|escape:'html'}">{$product.name|escape:'html'}</a>
                  </h3>

                  {if $product.reference}
                    <span class="dov-ref text-muted">Réf. {$product.reference|escape:'html'}</span>
                  {/if}

                  <div class="dov-product-price">{$product.price_formatted|escape:'html'}</div>

                  {if !$product.in_stock}
                    <span class="badge badge-warning">{l s='Sur commande' mod='dovehicle'}</span>
                  {/if}

                  <a href="{$product.link|escape:'html'}" class="btn btn-primary btn-sm mt-2 w-100">
                    {l s='Voir le produit' mod='dovehicle'}
                  </a>
                </div>

              </article>
            </div>
          {/foreach}
        </div>

        {* Pagination *}
        {if $dovehicle_total_pages > 1}
          <nav class="dov-pagination mt-3" aria-label="Pagination">
            <ul class="pagination justify-content-center">
              {for $p=1 to $dovehicle_total_pages}
                <li class="page-item {if $p == $dovehicle_page}active{/if}">
                  <a class="page-link"
                     href="{$urls.current_url|escape:'html'}?id_manufacturer={$dovehicle_id_manufacturer}&id_model={$dovehicle_id_model}&id_engine={$dovehicle_id_engine}{if $dovehicle_id_family}&id_family={$dovehicle_id_family}{/if}&page={$p}">
                    {$p}
                  </a>
                </li>
              {/for}
            </ul>
          </nav>
        {/if}

      {else}

        <div class="alert alert-info">
          <strong>{l s='Aucun produit trouvé' mod='dovehicle'}</strong><br>
          {l s='Aucun produit n\'est référencé pour ce véhicule.' mod='dovehicle'}
          {if $dovehicle_id_family}
            <a href="{$urls.current_url|escape:'html'}?id_manufacturer={$dovehicle_id_manufacturer}&id_model={$dovehicle_id_model}&id_engine={$dovehicle_id_engine}">
              {l s='Voir toutes les catégories' mod='dovehicle'}
            </a>
          {/if}
        </div>

      {/if}

    </div>{* /col produits *}
  </div>{* /row *}

</section>
{/block}
