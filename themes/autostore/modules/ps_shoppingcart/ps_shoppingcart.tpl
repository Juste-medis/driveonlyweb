{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<div id="_desktop_cart">
  {*<input type="checkbox" id="toggle-cart" class="no-style">*}
  <div class="blockcart cart-preview {if $cart.products_count > 0}active{else}inactive{/if}" data-refresh-url="{$refresh_url}">
    {*<label class="cart-header hidden-lg-up" for="toggle-cart">
        <div class="inner-wrapper">
            <i class="font-cart"></i>
            <span class="cart-products-count">{$cart.products_count}</span>
        </div>
    </label>*}
      {if $cart.products_count > 0}
        <a class="cart-header d-block -active" rel="nofollow" title="{l s='Checkout' d='Shop.Theme.Actions'}" href="{$cart_url}">
      {else}
      <span class="cart-header d-block">
      {/if}
        <div class="inner-wrapper">
            <i class="font-cart"></i>
            <span class="cart-title hidden-lg-down">{l s='Cart' d='Shop.Theme.Checkout'}</span>
            <span class="divider hidden-lg-down">{l s='-' d='Shop.Theme.Checkout'}</span>
            <span class="cart-products-count">{$cart.products_count}</span>
            <span class="hidden-md-down">{l s='item(s)' d='Shop.Theme.Checkout'}</span>
        </div>
      {if $cart.products_count > 0}
        </a>
      {else}
      </span>
      {/if}
    <div class="body cart-hover-content">
        <div class="container">
             <ul class="cart-list">
             {foreach from=$cart.products item=product}
                 <li class="cart-wishlist-item">
                 {include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}
                 </li>
             {/foreach}
             </ul>
             <div class="cart-footer">
                 <div class="cart-subtotals">
                     {foreach from=$cart.subtotals item="subtotal"}
                         {if $subtotal && $subtotal.value|count_characters > 0}
                         <div class="{$subtotal.type}">
                             <span class="value">{if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value}</span>
                             <span class="label">{$subtotal.label}</span>
                         </div>
                         {/if}
                     {/foreach}
                    <div class="cart-total">
                         <span class="value">{$cart.totals.total.value}</span>
                         <span class="label">{$cart.totals.total.label}</span>
                    </div>
                 </div>
                 <div class="cart-wishlist-action">
                     {*<a class="cart-wishlist-viewcart" href="{$cart_url}">view cart</a>*}
                     <a class="btn fill cart-wishlist-checkout" href="{$cart_url}"{*href="{$urls.pages.order}"*}>{l s='Checkout' d='Shop.Theme.Actions'}</a>
                 </div>
             </div>
         </div>
     </div>
  </div>
</div>

