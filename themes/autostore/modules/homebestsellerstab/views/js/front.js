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
    var owlBestItems = $(".js-carousel-best");
    if (owlBestItems.data('carousel')){
        owlBestItems.owlCarousel({
            items: owlBestItems.data('col'),
            nav: (owlBestItems.children().length > owlBestItems.data('col')) ? owlBestItems.data('arrows') : false,
            dots: (owlBestItems.children().length > owlBestItems.data('col')) ? owlBestItems.data('pag') : false,
            autoplay: owlBestItems.data('autoplay'),
            autoplayTimeout: owlBestItems.data('speed'),
            loop: (owlBestItems.children().length > 1) ? owlBestItems.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlBestItems.data('col_576')
                },
                576:{
                    items: owlBestItems.data('col_769')
                },
                769:{
                    items: owlBestItems.data('col_992')
                },
                992:{
                    items: owlBestItems.data('col_1200')
                },
                1200:{
                    items: owlBestItems.data('col')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});