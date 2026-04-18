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

{if $htmlbanners5.slides}
  <div id="htmlbanners5" class="testimonials-wrapper">
    <div class="testimonials-bg"></div>
    <div class="container wow fadeInUp" data-wow-offset="100">
      <h3 class="headline-section">
          {l s='Testimonials' d='Modules.Testimonials.Shop'}
      </h3>
      <p class="description-section">
        {l s='Our customers love us' d='Modules.Testimonials.Shop'}
      </p>
      <div class="htmlbanners5-inner js-htmlbanners5-carousel{if $htmlbanners5.carousel_active == 'true'} htmlbanners5-carousel owl-carousel{else} view-grid{/if} row" {if $htmlbanners5.carousel_active == 'true'} data-carousel={$htmlbanners5.carousel_active} data-autoplay={$htmlbanners5.autoplay} data-timeout="{$htmlbanners5.speed}" data-pause="{$htmlbanners5.pause}" data-pagination="{$htmlbanners5.pagination}" data-navigation="{$htmlbanners5.navigation}" data-loop="{$htmlbanners5.wrap}" data-items="{$htmlbanners5.items}" data-items_1199="{$htmlbanners5.items_1199}" data-items_991="{$htmlbanners5.items_991}" data-items_768="{$htmlbanners5.items_768}" data-items_480="{$htmlbanners5.items_480}"{/if}>
          {foreach from=$htmlbanners5.slides item=slide name='htmlbanners5'}
            <div class="testimonials-item {$slide.customclass}">
              <div class="testimonials-inner">
                <div class="testimonials-description">
                  {if $slide.description}
                      {$slide.description nofilter}
                  {/if}
                  {if $slide.url != $slide.url_base}
                  <a class="avatar-wrapper" href="{$slide.url}" title="{$slide.legend|escape}">
                  {else}
                  <div class="avatar-wrapper">
                  {/if}
                    {if $slide.image_url != $slide.image_base_url}
                    <img class="avatar" src="{$slide.image_url}" alt="{$slide.legend|escape}">
                    {/if}
                  {if $slide.url != $slide.url_base}
                  </a>
                  {else}
                  </div>
                  {/if}
                </div>
              </div>
            </div>
          {/foreach}
        </div>
    </div>
  </div>
{/if}

