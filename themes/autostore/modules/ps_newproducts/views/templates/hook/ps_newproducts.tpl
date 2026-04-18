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

<section class="featured-products column-block clearfix">
  <p class="h6 text-uppercase">
    {l s='New products' d='Shop.Theme.Catalog'}
  </p>
  <div class="toggle-block">
    <div class="products-list">
      {foreach from=$products item="product"}
        {include file="catalog/_partials/miniatures/product.tpl" product=$product}
      {/foreach}
    </div>
    <a class="all-product-link btn btn-primary btn_skine-two" href="{$allNewProductsLink}">
      {l s='All new products' d='Shop.Theme.Catalog'}
    </a>
  </div>
</section>

