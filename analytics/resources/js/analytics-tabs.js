$(document).ready(function() {
	$('.analyticsTabs').each(function(k, v) {

		$('.analyticsNav a', v).each(function(k2, v2) {

			$(v2).click(function() {

				$('.analyticsNav a', v).removeClass('active');

				$(this).addClass('active');

				$('.analyticsTab', v).addClass('hidden');

				$($('.analyticsTab', v).get(k2)).removeClass('hidden');


				// will re-draw the chart

				$(window).resize();

				return false;
			});
		});

		$('.analyticsNav li:first-child a', v).trigger('click');
	});

	// more

	$('.analyticsTabs').each(function(k, tab) {

		$('a.more, a.less', tab).click(function() {
			if($('div.more', tab).hasClass('hidden')) {
				$('div.more', tab).removeClass('hidden');
				$('a.more', tab).addClass('hidden');
			} else {
				$('div.more', tab).addClass('hidden');
				$('a.more', tab).removeClass('hidden');
			}

			$(window).resize();

			return false;
		});


	}) ;

});
