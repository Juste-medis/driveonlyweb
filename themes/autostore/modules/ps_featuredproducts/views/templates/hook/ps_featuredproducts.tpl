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
<div class="featured-products none-in-tabs wow fadeInUp" data-wow-offset="150">
  {if $page.page_name == 'index'}
  <div class="container -responsive">
  {/if}
  <p class="headline-section products-title">
    {l s='Featured products' d='Shop.Theme.Catalog'}
  </p>
  <div class="products grid row view-carousel js-carousel-products owl-carousel">
    {foreach from=$products item="product"}
      {include file="catalog/_partials/miniatures/product.tpl" product=$product}
    {/foreach}
  </div>
  {*<div class="text-center">
  <a class="more-btn btn" href="{$allProductsLink}">
    {l s='All products' d='Shop.Theme.Catalog'}
  </a>
  </div>*}
  {if $page.page_name == 'index'}
  </div>
  {/if}
</div>
