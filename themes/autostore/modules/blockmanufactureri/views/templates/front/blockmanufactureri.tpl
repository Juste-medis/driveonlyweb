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

<!-- Block manufacturers module -->
<div id="manufacturers-home" class="manufacturers-home">
	<div class="container wow slideInRight" data-wow-offset="200">
		<h3 class="headline-section">{if $display_link_manufacturer}<a href="{$link->getPageLink('manufacturer')}" title="{l s='Manufacturers' d='Modules.Blockmanufactureri.Shop'}">{/if}{l s='Featured manufacturers' d='Modules.Blockmanufactureri.Shop'}{if $display_link_manufacturer}</a>{/if}</h3>
		<p class="description-section">
			{l s='Curabitur consectetur augue nisi, viverra dapibus ligula sodales sed. Donec rhoncus, nisi non rutrum lobortis, magna eros mattis metus, ut egestas nisl arcu ac mi.' d='Modules.Blockmanufactureri.Shop'}
		</p>
	{if $manufacturers}
		<div class="manufacturers-list js-man-carousel{if $text_list} carousel-view owl-carousel{else} grid-view row{/if}" {if $text_list}data-carousel="{$text_list}"{/if}>
		{foreach from=$manufacturers item=manufacturer name=manufacturer_list}
			{if $smarty.foreach.manufacturer_list.iteration <= $text_list_nb}
			<div class="manufacturer-items{if $text_list == false} col-lg-3 col-sm-4 col-6 mb-3{/if}">
		        <a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)}" title="{l s='More about' d='Modules.Blockmanufactureri.Shop'} {$manufacturer.name}">
		        	<img src="{if $psversion < '1.7.0.0'}{$img_manu_dir}{else}{$urls.img_manu_url}{/if}{$manufacturer.id_manufacturer}-manufacturer_default.jpg" alt=" {$manufacturer.name}" />
		        </a>
		        {*<h5><a href="{$link->getmanufacturerLink($manufacturer.id_manufacturer, $manufacturer.link_rewrite)}" title="{l s='More about' d='Modules.Blockmanufactureri.Shop'} {$manufacturer.name}"> {$manufacturer.name|escape:'htmlall':'UTF-8'}</a>
		        </h5>*}
	        </div>
			{/if}
		{/foreach}
		</div>
	{else}
		<p>{l s='No manufacturer' d='Modules.Blockmanufactureri.Shop'}</p>
	{/if}
	</div>
</div>