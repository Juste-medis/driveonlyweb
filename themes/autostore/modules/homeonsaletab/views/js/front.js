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
    var owlSaleItems = $(".js-carousel-sale");
    if (owlSaleItems.data('carousel')){
        owlSaleItems.owlCarousel({
            items: owlSaleItems.data('col'),
            nav: (owlSaleItems.children().length > owlSaleItems.data('col')) ? owlSaleItems.data('arrows') : false,
            dots: (owlSaleItems.children().length > owlSaleItems.data('col')) ? owlSaleItems.data('pag') : false,
            autoplay: owlSaleItems.data('autoplay'),
            autoplayTimeout: owlSaleItems.data('speed'),
            loop: (owlSaleItems.children().length > 1) ? owlSaleItems.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlSaleItems.data('col_576')
                },
                576:{
                    items: owlSaleItems.data('col_769')
                },
                769:{
                    items: owlSaleItems.data('col_992')
                },
                992:{
                    items: owlSaleItems.data('col_1200')
                },
                1200:{
                    items: owlSaleItems.data('col')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});