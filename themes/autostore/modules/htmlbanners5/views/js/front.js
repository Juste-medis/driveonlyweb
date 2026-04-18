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
    var owlTestimonials = $(".js-htmlbanners5-carousel");
    if (owlTestimonials.data('carousel')){
        owlTestimonials.owlCarousel({
            autoplaySpeed: 1000,
            navSpeed: 1000,
            dotsSpeed: 1000,
            items: owlTestimonials.data('items'),
            nav: (owlTestimonials.children().length > 1) ? owlTestimonials.data('navigation') : false,
            dots: (owlTestimonials.children().length > owlTestimonials.data('items')) ? owlTestimonials.data('pagination') : false,
            autoplay: owlTestimonials.data('autoplay'),
            autoplayTimeout: owlTestimonials.data('timeout'),
            autoplayHoverPause: owlTestimonials.data('pause'),
            loop: (owlTestimonials.children().length > 1) ? owlTestimonials.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            responsive:{
                0:{
                    items: owlTestimonials.data('items_480')
                },
                480:{
                    items: owlTestimonials.data('items_768')
                },
                768:{
                    items: owlTestimonials.data('items_991')
                },
                991:{
                    items: owlTestimonials.data('items_1199')
                },
                1199:{
                    items: owlTestimonials.data('items')
                }
            },
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">']
        });
    }
});