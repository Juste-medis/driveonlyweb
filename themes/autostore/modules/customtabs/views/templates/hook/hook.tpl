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

{if $customtabs.slides}
    {foreach from=$customtabs.slides item=slide name='customtabs'}
      {assign var=productsId value=","|explode:$slide.category}
      {foreach $productsId as $selectedId}
        {if $selectedId == $customtabs.product_id || $slide.category == ''}
          <div class="tab-pane fade in {$slide.customclass}" id="custom_tab_{$smarty.foreach.customtabs.iteration}" data-current="{$customtabs.product_id}" data-selected="{$slide.category}">
            <div class="tab-pane-inner rte">
              {if ($slide.image_url != $slide.image_base_url) && $slide.url != $slide.url_base}
              <a class="banner-link" href="{$slide.url}" {*title="{$slide.legend|escape}"*}>
              {/if}
              {if $slide.image_url != $slide.image_base_url}
              <img class="img-banner" src="{$slide.image_url}" alt="{$slide.legend|escape:'htmlall':'UTF-8'}">
              {/if}
              {if ($slide.image_url != $slide.image_base_url) && $slide.url != $slide.url_base}
              </a>
              {/if}
              {if $slide.description}
                  {$slide.description nofilter}
              {/if}
            </div>
          </div>
        {/if}
      {/foreach}
    {/foreach}
{/if}


