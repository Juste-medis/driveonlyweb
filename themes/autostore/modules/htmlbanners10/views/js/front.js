/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

jQuery(document).ready(function() {
    var owlFeatures = $(".js-htmlbanners10-carousel");
    if (owlFeatures.data('carousel')){
        owlFeatures.owlCarousel({
            autoplaySpeed: 800,
            navSpeed: 800,
            dotsSpeed: 800,
            items: owlFeatures.data('items'),
            nav: (owlFeatures.children().length > 1) ? owlFeatures.data('navigation') : false,
            dots: (owlFeatures.children().length > owlFeatures.data('items')) ? owlFeatures.data('pagination') : false,
            autoplay: owlFeatures.data('autoplay'),
            autoplayTimeout: owlFeatures.data('timeout'),
            autoplayHoverPause: owlFeatures.data('pause'),
            loop: (owlFeatures.children().length > 1) ? owlFeatures.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlFeatures.data('items_480')
                },
                480:{
                    items: owlFeatures.data('items_768')
                },
                768:{
                    items: owlFeatures.data('items_991')
                },
                991:{
                    items: owlFeatures.data('items_1199')
                },
                1199:{
                    items: owlFeatures.data('items')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});