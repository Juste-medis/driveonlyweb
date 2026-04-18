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

{if isset($wishlists) && (null != $wishlists && count($wishlists) > 1)}
    <div class="panel-product-actions">
    	<div id="wishlist_button">
    		<button class="wishlist_button_extra wishlist-btn" onclick="WishlistCart('wishlist_block_list', 'add', '{$id_product|intval}', $('#idCombination').val(), document.getElementById('quantity_wanted').value, $('#idWishlist').val()); return false;"  title="{l s='Add to wishlist' d='Modules.Blockwishlist.Shop'}">
    			<i class="font-heart"></i>
                {*{l s='Add to wishlist' d='Modules.Blockwishlist.Shop'}*}
    		</button>
            <select id="idWishlist">
    			{foreach $wishlists as $wishlist}
    				<option value="{$wishlist.id_wishlist}">{$wishlist.name}</option>
    			{/foreach}
    		</select>
    	</div>
    </div>
{else}
    <div class="panel-product-actions">
    	<a id="wishlist_button" class="wishlist-btn" href="#" onclick="WishlistCart('wishlist_block_list', 'add', '{$id_product|intval}', $('#idCombination').val(), document.getElementById('quantity_wanted').value); return false;" rel="nofollow"  title="{l s='Add to my wishlist' d='Modules.Blockwishlist.Shop'}">
    		<i class="font-heart"></i>
            {*{l s='Add to wishlist' d='Modules.Blockwishlist.Shop'}*}
    	</a>
    </div>
{/if}
