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

{if $htmlbanners8.slides}
  <div id="htmlbanners8" class="category-banners">
    <div class="htmlbanners8-inner js-htmlbanners8-carousel {if $htmlbanners8.carousel_active == 'true'}htmlbanners8-carousel owl-carousel {/if}row clearfix" {if $htmlbanners8.carousel_active == 'true'} data-carousel={$htmlbanners8.carousel_active} data-autoplay={$htmlbanners8.autoplay} data-timeout="{$htmlbanners8.speed}" data-pause="{$htmlbanners8.pause}" data-pagination="{$htmlbanners8.pagination}" data-navigation="{$htmlbanners8.navigation}" data-loop="{$htmlbanners8.wrap}" data-items="{$htmlbanners8.items}" data-items_1199="{$htmlbanners8.items_1199}" data-items_991="{$htmlbanners8.items_991}" data-items_768="{$htmlbanners8.items_768}" data-items_480="{$htmlbanners8.items_480}"{/if}>
      {foreach from=$htmlbanners8.slides item=slide name='htmlbanners8'}
        {assign var=categoriesIds value=","|explode:$slide.category}
        {foreach $categoriesIds as $selectedId}
          {if $selectedId == $htmlbanners8.category_id || $slide.category == ''}
            <div class="category-banner {$slide.customclass}" data-selected="{$selectedId}" data-current="{$htmlbanners8.category_id}">
              {if $slide.url != $slide.url_base}
              <a class="banner-link" href="{$slide.url}" {*title="{$slide.legend|escape}"*}>
              {else}
              <div class="banner-link">
              {/if}
              {if $slide.image_url}
              <figure>
              <img class="img-banner" src="{$slide.image_url}" alt="{$slide.legend|escape}">
              {/if}
                  {if $slide.description}
                    <figcaption class="banner-description">
                        {$slide.description nofilter}
                    </figcaption>
                  {/if}
              {if $slide.image_url}
              </figure>
              {/if}
              {if $slide.url != $slide.url_base}
              </a>
              {else}
              </div>
              {/if}
            </div>
          {/if}
        {/foreach}
      {/foreach}
    </div>
  </div>
{/if}