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
    var manCarousel = $(".js-man-carousel");
    if (manCarousel.data('carousel')){
        manCarousel.owlCarousel({
            items: 5,
            nav: (manCarousel.children().length > 1) ? true : false,
            dots: false,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplaySpeed: 800,
            navSpeed: 600,
            loop: (manCarousel.children().length > 1) ? true : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: 2
                },
                480:{
                    items: 2
                },
                768:{
                    items: 3
                },
                991:{
                    items: 4
                },
                1199:{
                    items: 5
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});