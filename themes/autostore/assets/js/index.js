$(document).ready(function(){
	/*Animation of blocks on scroll*/
	var wow = new WOW(
	{
		mobile: false,
	}
	);
	wow.init();
	/*product tabs on index page*/
	$('.js-products-tabs li:first a, .js-wrap-products-tabs .tab-pane:first').addClass('active');
	/*end product tabs on index page*/
	//column products
	var productsfromcategory = $('.js-productsfromcategory');
	if (productsfromcategory.length != 0) {
		var columnProducts = productsfromcategory.bxSlider({
			mode: 'vertical',
			minSlides : 4,
			maxSlides : 4,
			moveSlides : 1,
			auto: true,
			pause: 5000,
			controls: true,
			pager: false,
			responsive: true,
			prevText: '',
			nextText: ''
			//ticker: true,
			//speed: 20000,
			//tickerHover: true
		});
	}
});