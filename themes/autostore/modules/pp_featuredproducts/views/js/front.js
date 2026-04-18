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
    var owlFeaturedItems = $(".js-carousel-ppfeatured");
    if (owlFeaturedItems.data('carousel')){
        owlFeaturedItems.owlCarousel({
            items: owlFeaturedItems.data('col'),
            nav: (owlFeaturedItems.children().length > owlFeaturedItems.data('col')) ? owlFeaturedItems.data('arrows') : false,
            dots: (owlFeaturedItems.children().length > owlFeaturedItems.data('col')) ? owlFeaturedItems.data('pag') : false,
            autoplay: owlFeaturedItems.data('autoplay'),
            autoplayTimeout: owlFeaturedItems.data('speed'),
            loop: (owlFeaturedItems.children().length > 1) ? owlFeaturedItems.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlFeaturedItems.data('col_576')
                },
                576:{
                    items: owlFeaturedItems.data('col_769')
                },
                769:{
                    items: owlFeaturedItems.data('col_992')
                },
                992:{
                    items: owlFeaturedItems.data('col_1200')
                },
                1200:{
                    items: owlFeaturedItems.data('col')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});