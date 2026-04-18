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
<script type="text/javascript">
var wishlistProductsIds='';
var baseDir ='{$content_dir}';
var static_token='{$static_token}';
var isLogged ='{$isLogged}';
var loggin_required='{{l s='You must be logged in to manage your wishlist.' d='Modules.Blockwishlist.Shop' js=1}|trim}';
var added_to_wishlist ='{{l s='The product was successfully added to your wishlist.' d='Modules.Blockwishlist.Shop' js=1}|trim}';
var mywishlist_url='{{$link->getModuleLink('blockwishlist', 'mywishlist', array(), true)|escape:'quotes':'UTF-8'}|trim}';
{if isset($isLogged)&&$isLogged}
	var isLoggedWishlist=true;
{else}
	var isLoggedWishlist=false;
{/if}
</script>
{*<div id="_desktop_wishlist_top">
	<a class="wishtlist_top" href="{$link->getModuleLink('blockwishlist', 'mywishlist', array(), true)|escape:'html':'UTF-8'}">
		{l s='Wish list' d='Modules.Blockwishlist.Shop'}
	    <span class="cart-wishlist-number">{$count_product}</span>
	</a>
</div>*}

