{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div id="_desktop_contact_link">
    <div class="header__contact dropdown-mobile">
        <span class="js-toggle btn-toggle-mobile font-phone hidden-lg-up"></span>
        <div class="js-toggle-list header__contact__list dropdown-toggle-mobile">
            {if $contact_infos.phone}
            <a class="header__contact__item" href="tel:{$contact_infos.phone}" title="{l s='Contact us' d='Shop.Theme.Global'}">
                <i class="font-phone hidden-md-down"></i>
                {$contact_infos.phone}
            </a>
            {/if}
            {*{if isset($display_email)}
                {if $contact_infos.email && $display_email}
                    <a class="header__contact__item" href="mailto:{$contact_infos.email}" title="{l s='Contact us' d='Shop.Theme.Global'}" target="_blank">
                        <i class="font-envelope hidden-md-down"></i>
                        {$contact_infos.email}
                    </a>
                {/if}
            {else}
                {if $contact_infos.email}
                    <a class="header__contact__item" href="mailto:{$contact_infos.email}" title="{l s='Contact us' d='Shop.Theme.Global'}" target="_blank">
                        <i class="font-envelope hidden-md-down"></i>
                        {$contact_infos.email}
                    </a>
                {/if}
            {/if}*}
            {*
                <a class="header__contact__item" href="{$urls.pages.contact}">
                    <i class="material-icons hidden-md-down">&#xE3C9;</i>
                    {l s='Contact us' d='Shop.Theme.Global'}
                </a>
            *}
        </div>
    </div>
</div>
