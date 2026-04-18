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
    var arrayAnimIn = ['', 'bounceIn', 'bounceInLeft', 'bounceInRight', 'bounceInUp', 'fadeIn', 'fadeInDown', 'fadeInDownBig', 'fadeInLeft', 'fadeInLeftBig', 'fadeInRight', 'fadeInRightBig', 'fadeInUp', 'fadeInUpBig', 'flipInX', 'flipInY', 'lightSpeedIn', 'rotateIn', 'rotateInDownLeft', 'rotateInDownRight', 'rotateInUpLeft', 'rotateInUpRight', 'slideInUp', 'slideInDown', 'slideInLeft', 'slideInRight', 'zoomIn', 'zoomInDown', 'zoomInLeft', 'zoomInRight', 'zoomInUp', 'rollIn'];
    var arrayAnimOut = ['', 'bounceOut', 'bounceOutLeft', 'bounceOutRight', 'bounceOutUp', 'fadeOut', 'fadeOutDown', 'fadeOutDownBig', 'fadeOutLeft', 'fadeOutLeftBig', 'fadeOutRight', 'fadeOutRightBig', 'fadeOutUp', 'fadeOutUpBig', 'flipOutX', 'flipOutY', 'lightSpeedOut', 'rotateOut', 'rotateOutDownLeft', 'rotateOutDownRight', 'rotateOutUpLeft', 'rotateOutUpRight', 'slideOutUp', 'slideOutDown', 'slideOutLeft', 'slideOutRight', 'zoomOut', 'zoomOutDown', 'zoomOutLeft', 'zoomOutRight', 'zoomOutUp', 'rollOut'];
    var owlHtmlbanners9 = $("#htmlbanners9");
        owlHtmlbanners9.owlCarousel({
            items: 1,
            nav: (owlHtmlbanners9.children().length > 1) ? owlHtmlbanners9.data('navigation') : false,
            dots: (owlHtmlbanners9.children().length > 1) ? owlHtmlbanners9.data('pagination') : false,
            autoplay: owlHtmlbanners9.data('autoplay'),
            autoplayTimeout: owlHtmlbanners9.data('timeout'),
            autoplayHoverPause: owlHtmlbanners9.data('pause'),
            loop: (owlHtmlbanners9.children().length > 1) ? owlHtmlbanners9.data('loop') : false,
            rewind: true,
            responsiveClass: true,
            /*autoplaySpeed: owlHtmlbanners9.data('speed'),*/
            /*navSpeed: owlHtmlbanners9.data('speed'),*/
            /*dotsSpeed: owlHtmlbanners9.data('speed'),*/
            navText: ['<i class="font-arrow-left">','<i class="font-arrow-right">'],
            animateIn: arrayAnimIn[owlHtmlbanners9.data('anim_in')],
            animateOut: arrayAnimOut[owlHtmlbanners9.data('anim_out')]
        });
});