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
    var owlNewItems = $(".js-carousel-ppnew");
    if (owlNewItems.data('carousel')){
        owlNewItems.owlCarousel({
            items: owlNewItems.data('col'),
            nav: (owlNewItems.children().length > owlNewItems.data('col')) ? owlNewItems.data('arrows') : false,
            dots: (owlNewItems.children().length > owlNewItems.data('col')) ? owlNewItems.data('pag') : false,
            autoplay: owlNewItems.data('autoplay'),
            autoplayTimeout: owlNewItems.data('speed'),
            loop: (owlNewItems.children().length > 1) ? owlNewItems.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlNewItems.data('col_576')
                },
                576:{
                    items: owlNewItems.data('col_769')
                },
                769:{
                    items: owlNewItems.data('col_992')
                },
                992:{
                    items: owlNewItems.data('col_1200')
                },
                1200:{
                    items: owlNewItems.data('col')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});