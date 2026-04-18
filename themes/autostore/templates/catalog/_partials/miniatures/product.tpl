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
{block name='product_miniature_item'}
  <article class="product-miniature js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" itemscope itemtype="http://schema.org/Product">
    <div class="thumbnail-container">
      <div class="thumbnail-wrapper">
      {block name='product_thumbnail'}
        <a href="{$product.url}" class="thumbnail product-thumbnail">
          {foreach name="thumbnails" from=$product.images item=image}
            {if $smarty.foreach.thumbnails.iteration == 2}
              <img
                class="thumbnail-alternate"
                src="{$image.bySize.home_default.url}"
                alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:57:'...'}{/if}"
                width="{$product.cover.bySize.home_default.width}"
                height="{$product.cover.bySize.home_default.height}"
              >
            {/if}
          {/foreach}
          {if $product.cover}
          <img
            class="thumbnail-img"
            src="{$product.cover.bySize.home_default.url}"
            alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:57:'...'}{/if}"
            data-full-size-image-url="{$product.cover.large.url}"
            width="{$product.cover.bySize.home_default.width}"
            height="{$product.cover.bySize.home_default.height}"
            itemprop="image"
          >
          {else}
          <img
              class="thumbnail-img"
              src="{$urls.no_picture_image.bySize.home_default.url}"
            >
          {/if}
        </a>
      {/block}
        {block name='product_flags'}
        <ul class="product-flags">
          {foreach from=$product.flags item=flag}
            <li class="{$flag.type}">{$flag.label}</li>
          {/foreach}
          {if $product.has_discount}
              {if $product.discount_type === 'percentage'}
                <li class="discount-percentage">{$product.discount_percentage}</li>
              {elseif $product.discount_type === 'amount'}
                <li class="discount-percentage">{$product.discount_amount_to_display}</li>
              {/if}
          {/if}
        </ul>
        {/block}
      </div>
        {block name='product_variants'}
        {if $product.main_variants}
          {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
        {/if}
        {/block}
      <div class="right-block">
        <div class="product-desc">
          {block name='product_reviews'}
            {hook h='displayProductListReviews' product=$product}
          {/block}
          {block name='product_name'}
            <h3 class="h3 product-title" itemprop="name"><a href="{$product.url}">{$product.name|truncate:57:'...'}</a></h3>
          {/block}
          {block name='product_description'}
            <p class="product_desc" itemprop="description">{$product.description|strip_tags:'UTF-8'|truncate:200:'...'}</p>
          {/block}
          {if Manufacturer::getnamebyid($product.id_manufacturer)}
          <div itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
              <meta itemprop="name" content="{Manufacturer::getnamebyid($product.id_manufacturer)}"/>
          </div>
          {/if}
          {if $product.reference}
          <meta itemprop="sku" content="{$product.reference}" />
          {/if}
          {if $product.ean13}
            <meta itemprop="gtin13" content="{$product.ean13}" />
          {/if}
          {block name='product_price_and_shipping'}
            {if $product.show_price}
              <div class="product-price-and-shipping" itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                <link itemprop="url" href="{$product.url}" />
                  <meta itemprop="availability" content="{if $product.available_for_order == 1}https://schema.org/InStock{else}https://schema.org/OutOfStock{/if}" />
                  <meta itemprop="priceCurrency" content="{$currency.iso_code}" />
                {*From label*}
                  {hook h='displayProductPriceBlock' product=$product type="before_price"}
                {*End label*}
                {if $product.has_discount}
                  {hook h='displayProductPriceBlock' product=$product type="old_price"}
                  <span class="regular-price">{$product.regular_price}</span>
                {/if}
                <span itemprop="price" content="{$product.price_amount}" class="price">{$product.price}</span>

                {hook h='displayProductPriceBlock' product=$product type='unit_price'}

               {hook h='displayProductPriceBlock' product=$product type='weight'}
                {*Start adding tax and delivery labels*}
                {if $configuration.taxes_enabled && $configuration.display_taxes_label}
                  {$product.labels.tax_long}
                {/if}
                {hook h='displayProductPriceBlock' product=$product type="price"}
                {if $product.delivery_information}
                    {$product.delivery_information}
                {/if}
                {*End adding tax and delivery labels*}
            </div>
          {/if}
          {/block}
          </div>
          <div class="highlighted-informations{if !$product.main_variants} no-variants{/if}">
            <div class="inner">
             {if !$configuration.is_catalog}
             <form action="{$urls.pages.cart}" method="post" class="add-to-cart-or-refresh">
                 <input type="hidden" name="token" value="{$static_token}">
                 <input type="hidden" name="id_product" value="{$product.id}" class="product_page_product_id">
                 <input type="hidden" name="id_customization" value="0" class="product_customization_id">
                 <input type="hidden" name="qty" value="1" min="1">
                 <button class="add-cart font-cart{if !$product.add_to_cart_url} disabled{/if}" data-button-action="add-to-cart" type="submit" title="{l s='Add to cart' d='Shop.Theme.Actions'}">
                     <span>{l s='Add to cart' d='Shop.Theme.Actions'}</span>
                 </button>
             </form>
             {/if}
              {hook h='displayProductListFunctionalButtons' product=$product}
              {block name='quick_view'}
                <a class="quick-view" href="#" data-link-action="quickview" title="{l s='Quick view' d='Shop.Theme.Actions'}">
                  <i class="font-eye"></i><span>{l s='Quick view' d='Shop.Theme.Actions'}</span>
                </a>
              {/block}
              {block name='more_info'}
                  <a href="{$product.canonical_url}" class="link-view" title="{l s='More info' d='Shop.Theme.Actions'}">
                    <i class="font-more"></i>
                      <span>{l s='More info' d='Shop.Theme.Actions'}</span>
                  </a>
              {/block}
            </div>
          </div>
      </div>
     </div>
  </article>
{/block}
