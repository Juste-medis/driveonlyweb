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
{block name='header_banner'}
  <div class="header-banner">
    {hook h='displayBanner'}
  </div>
{/block}

{block name='header_nav'}
  <nav class="header-nav">
    <div class="container">
        <div class="row inner-wrapper">
          <div class="left-nav">
              {hook h='displayNav1'}
          </div>
          <div class="right-nav">
              {hook h='displayNav2'}
          </div>
          <div class="hidden-lg-up mobile">
            <div id="menu-icon">
                <span class="sw-topper"></span>
                <span class="sw-bottom"></span>
                <span class="sw-footer"></span>
            </div>
            <div class="top-logo" id="_mobile_logo"></div>
            {if Module::isInstalled(ps_contactinfo) && Module::isEnabled(ps_contactinfo)}
            <div id="_mobile_contact_link"></div>
            {/if}
            {if Module::isInstalled(ps_customersignin) && Module::isEnabled(ps_customersignin)}
            <div id="_mobile_user_info"></div>
            {/if}
            {if Module::isInstalled(ps_shoppingcart) && Module::isEnabled(ps_shoppingcart)}
            <div id="_mobile_cart"></div>
            {/if}
          </div>
        </div>
    </div>
  </nav>
{/block}

{block name='header_top'}
  <div class="header-top">
    <div class="container">
       <div class="row inner-wrapper hidden-md-down">
          <div id="_desktop_logo" class="col-md-3">
              {if $page.page_name == 'index'}
                <h1>
                  <a href="{$urls.base_url}">
                    <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
                    <span>{$shop.name}</span>
                  </a>
                </h1>
              {else}
                  <a href="{$urls.base_url}">
                    <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
                  </a>
              {/if}
          </div>
        {hook h='displayTop'}
      </div>
      <div id="mobile_top_menu_wrapper" class="row hidden-lg-up">
        <div class="wrapper-nav">
            {if Module::isInstalled(ps_currencyselector) && Module::isEnabled(ps_currencyselector)}
            <div id="_mobile_currency_selector"></div>
            {/if}
            {if Module::isInstalled(ps_languageselector) && Module::isEnabled(ps_languageselector)}
            <div id="_mobile_language_selector"></div>
            {/if}
            <div id="_mobile_link_block"></div>
        </div>
        {if Module::isInstalled(ps_searchbar) && Module::isEnabled(ps_searchbar)}
        <div class="wrapper-modules">
          <div id="_mobile_search_bar"></div>
        </div>
        {/if}
        <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
      </div>
    </div>
  </div>
  {hook h='displayNavFullWidth'}
{/block}
