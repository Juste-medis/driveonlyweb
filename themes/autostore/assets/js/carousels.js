$(document).ready(function(){
	$('.js-carousel-products').each(function() {
		var owlProducts = $(this);
		owlProducts.owlCarousel({
			items: 5,
			nav: false,
			dots: true,
			autoplay: true,
			loop: owlProducts.children().length > 1,
			rewind: true,
			responsiveClass:true,
			responsive:{
				0:{
					items:2
				},
				480:{
					items:2
				},
				768:{
					items:3
				},
				991:{
					items:4
				}
			},
			navText:	['<i class="font-arrow-left">','<i class="font-arrow-right">']
		});
	});
});