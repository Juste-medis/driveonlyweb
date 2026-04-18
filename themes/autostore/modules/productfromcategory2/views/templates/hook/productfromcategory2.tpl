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

<div id="productfromcategory2" class="products-block col-sm-6 col-lg-3 wow fadeInUp" data-wow-offset="300" data-wow-delay="0.2s">
    <p class="title">
        <a class="link" href="{$allProductsLink}" title="{$productfromcategory2_category_name|escape:'html':'UTF-8'}">
            {$productfromcategory2_category_name|escape:'html':'UTF-8'}
        </a>
    </p>
    <div class="items js-productsfromcategory">
        {if $products}
	        {foreach from=$products item="product"}
	            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
	        {/foreach}
        {else}
            <div class="col-md-12">
                <div class="alert alert-warning">
                    {l s='No featured products found' d='Modules.Homefeatured.Shop'}
                </div>
            </div>
        {/if}
    </div>
</div>
