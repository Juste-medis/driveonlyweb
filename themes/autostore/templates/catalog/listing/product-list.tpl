{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file=$layout}

{block name='content'}
  <section id="main">

    {*{block name='product_list_header'}
      <h2 class="h2">{$listing.label}</h2>
    {/block}*}
     <h2 class="page-heading product-listing catalog-title">
      {if $page.page_name == 'category'}
        <span>{$category.name}</span>
      {elseif $page.page_name == 'prices-drop'}
         <span>{l s='Prices drop' d='Shop.Theme.Catalog'}</span>
      {elseif $page.page_name == 'new-products'}
         <span>{l s='New products' d='Shop.Theme.Catalog'}</span>
    {elseif $page.page_name == 'best-sales'}
         <span>{l s='Best sellers' d='Shop.Theme.Catalog'}</span>
      {elseif $page.page_name == 'manufacturer'}
         <span>{$manufacturer.name}</span>
      {elseif $page.page_name == 'supplier'}
         <span>{$supplier.name}</span>
      {elseif $page.page_name == 'search'}
         <span>{l s='Search' d='Shop.Theme.Catalog'}</span>
      {/if}
      <span class="heading-counter">
        {if $listing.pagination.total_items > 1}
          {l s='There are %product_count% products.' d='Shop.Theme.Catalog' sprintf=['%product_count%' => $listing.pagination.total_items]}
        {elseif $listing.pagination.total_items > 0}
          {l s='There is 1 product.' d='Shop.Theme.Catalog'}
        {/if}
      </span>
    </h2>
      {block name='product_list_brand_description'}
      {if $page.page_name == 'manufacturer' && $manufacturer.description}
        <div id="manufacturer-short_description" class="rte">{$manufacturer.short_description nofilter}</div>
        <div id="manufacturer-description" class="rte">{$manufacturer.description nofilter}</div>
      {elseif $page.page_name == 'supplier' && $supplier.description}
        <div id="supplier-description" class="rte">{$supplier.description nofilter}</div>
      {/if}
      {/block}
      {block name='subcategory_list'}
        {if isset($subcategories) && $subcategories|@count > 0}
          {include file='catalog/_partials/subcategories.tpl' subcategories=$subcategories}
        {/if}
      {/block}
    <section id="products" class="grid">
      {if $listing.products|count}
          {block name='product_list_top'}
            {include file='catalog/_partials/products-top.tpl' listing=$listing}
          {/block}

        {block name='product_list_active_filters'}
          <div class="hidden-sm-down">
            {$listing.rendered_active_filters nofilter}
          </div>
        {/block}

          {block name='product_list'}
            {include file='catalog/_partials/products.tpl' listing=$listing}
          {/block}

          {block name='product_list_bottom'}
            {include file='catalog/_partials/products-bottom.tpl' listing=$listing}
          {/block}
      {else}
        {include file='errors/not-found.tpl'}
      {/if}
    </section>
    {if $page.page_name == 'category'}
        {hook h='displayHtmlContent4' category=$category}
    {/if}
  </section>
{/block}
