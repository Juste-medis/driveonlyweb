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

{if $htmlbanners3.slides}
  <div id="htmlbanners3" class="home-video wow fadeInDown" data-wow-offset="350">
    <div class="htmlbanners3-inner js-htmlbanners3-carousel {if $htmlbanners3.carousel_active == 'true'}htmlbanners3-carousel owl-carousel{/if}" {if $htmlbanners3.carousel_active == 'true'} data-carousel={$htmlbanners3.carousel_active} data-autoplay={$htmlbanners3.autoplay} data-timeout="{$htmlbanners3.speed}" data-pause="{$htmlbanners3.pause}" data-pagination="{$htmlbanners3.pagination}" data-navigation="{$htmlbanners3.navigation}" data-loop="{$htmlbanners3.wrap}" data-items="{$htmlbanners3.items}" data-items_1199="{$htmlbanners3.items_1199}" data-items_991="{$htmlbanners3.items_991}" data-items_768="{$htmlbanners3.items_768}" data-items_480="{$htmlbanners3.items_480}"{/if}>
      {foreach from=$htmlbanners3.slides item=slide name='htmlbanners3'}
        <div class="promo-home {$slide.customclass}">
            {assign var="has_iframe" value=$slide.description|regex_replace:'/.*<iframe.*/is' : 'iframe_found'}
            <div class="video-promo__wrapper embed-responsive embed-responsive-16by9{if $slide.image_url != $slide.image_base_url && $has_iframe != 'iframe_found'} -overlay-enabled{/if}">
              {if $slide.description}
                  {$slide.description nofilter}
              {/if}
              {if $slide.image_url != $slide.image_base_url && $has_iframe != 'iframe_found'}
                {assign var="has_video" value=$slide.description|regex_replace:'/.*<video.*/is' : 'video_found'}
                {if $has_video == 'video_found'}
                  <div class="video-promo__button-box"><strong class="video-promo__play -fade-out"> <em class="font-play"></em><span>Play</span></strong></div>
                  <div class="video-promo__stop"><i class="material-icons">pause_circle_outline</i></div>
                {/if}
                <div class="video-promo__poster -scroll-background" style="background-image: url({$slide.image_url});"></div>
              {/if}
            </div>
        </div>
      {/foreach}
    </div>
  </div>
{/if}