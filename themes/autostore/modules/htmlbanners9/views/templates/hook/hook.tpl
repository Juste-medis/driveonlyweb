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

{if $htmlbanners9.slides}
  <div id="htmlbanners9" class="headerslider owl-carousel wow fadeIn" data-fullscreen={$htmlbanners9.fullscreen} data-autoplay={$htmlbanners9.autoplay} data-timeout="{$htmlbanners9.speed}" {*data-speed="{$htmlbanners9.slide_speed}"*} data-pause="{$htmlbanners9.pause}" data-pagination="{$htmlbanners9.pagination}" data-navigation="{$htmlbanners9.navigation}" data-loop="{$htmlbanners9.wrap}" data-anim_in="{$htmlbanners9.anim_in}" data-anim_out="{$htmlbanners9.anim_out}">
      {foreach from=$htmlbanners9.slides item=slide name='htmlbanners9'}
        <div class="header-slide{if $htmlbanners9.fullscreen == 'true'} fullscreen-mode{else} default-mode{/if} {$slide.customclass}">
          {if $slide.url != $slide.url_base}
            <a class="slide-link" href="{$slide.url}" {*title="{$slide.legend|escape}"*}>
          {else}
            <div class="slide-link">
          {/if}
          {if $slide.image_url != $slide.image_base_url}
            <div class="headerslider-figure">
          {if $htmlbanners9.fullscreen != 'true'}
              <img class="headerslider-img" src="{$slide.image_url}" alt="{$slide.legend|escape}">
          {else}
              <div class="headerslider-img parallax-bg" style="background-image: url({$slide.image_url});"></div>
          {/if}
          {/if}
              {if $slide.description}
                <div class="caption-description">
                    {$slide.description nofilter}
                </div>
              {/if}
          {if $slide.image_url != $slide.image_base_url}
            </div>
          {/if}
          {if $slide.url != $slide.url_base}
            </a>
          {else}
          </div>
          {/if}
        </div>
      {/foreach}
  </div>
{/if}

