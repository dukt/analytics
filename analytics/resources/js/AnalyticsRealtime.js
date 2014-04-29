$(document).ready(function() {
	var refreshInterval = eval(Analytics.realtimeRefreshInterval);

	// console.log('realtime');

	analyticsRealtimeRequest();

	if(refreshInterval == null) {
		refreshInterval = 10; // Default to 10 seconds
	} else if(refreshInterval<2) { // mini 2 seconds
		refreshInterval = 100;
	}

	refreshInterval = refreshInterval * 1000; // to (ms)

	setInterval(analyticsRealtimeRequest, refreshInterval);
});

function analyticsRealtimeRequest()
{
	$('.analytics-widget-realtime').parent().parent().addClass('loading');

	var data = {

	};

	// console.log('send realtime request', data);

	Craft.postActionRequest('analytics/charts/realtime', data, function(response) {

		// realtime
		// console.log('get realtime response', response);

		$('.analytics-widget-realtime .analytics-errors-inject').html('');

		if(typeof(response.error) != 'undefined') {
			$('.analytics-widget-realtime .analytics-errors').removeClass('hidden');
			$('.analytics-widget-realtime .analytics-widget').addClass('hidden');

			$('<p class="error">'+response.error.message+'</p>').appendTo('.analytics-widget-realtime .analytics-errors-inject');
		} else {

			$('.analytics-widget-realtime .analytics-errors').addClass('hidden');
			$('.analytics-widget-realtime .analytics-widget').removeClass('hidden');


			var newVisitor = response.visitorType.newVisitor;
			var returningVisitor = response.visitorType.returningVisitor;

			var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

			$('.analytics-widget-realtime .active-visitors .count').text(calcTotal);

			if (calcTotal > 0) {
				$('.analytics-widget-realtime .progress').removeClass('hidden');
				$('.analytics-widget-realtime .legend').removeClass('hidden');
			} else {
				$('.analytics-widget-realtime .progress').addClass('hidden');
				$('.analytics-widget-realtime .legend').addClass('hidden');
			}

			if(calcTotal > 0)
			{
				var blue = Math.round(100 * newVisitor / calcTotal);
			}
			else
			{
				var blue = 100;
			}

			var green = 100 - blue;

			// blue

			$('.analytics-widget-realtime .progress-bar.blue').css('width', blue+'%');
			$('.analytics-widget-realtime .progress-bar.blue span').text(blue+'%');

			if(blue > 0)
			{
				$('.analytics-widget-realtime .progress-bar.blue').removeClass('hidden');
			}
			else
			{
				$('.analytics-widget-realtime .progress-bar.blue').addClass('hidden');
			}

			// green

			$('.analytics-widget-realtime .progress-bar.green').css('width', green+'%');
			$('.analytics-widget-realtime .progress-bar.green span').text(green+'%');

			if(green > 0)
			{
				$('.analytics-widget-realtime .progress-bar.green').removeClass('hidden');
			}
			else
			{
				$('.analytics-widget-realtime .progress-bar.green').addClass('hidden');
			}

			// realtime content


			if (calcTotal > 0) {
				$('.no-active-visitors').addClass('hidden');

				// content

				$('.analytics-realtime-content table').removeClass('hidden');
				$('.analytics-realtime-content tbody').html('');

				$.each(response.content, function(k,v) {
					var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

					$('.analytics-realtime-content tbody').append(row);
				});

				// sources

				$('.analytics-realtime-sources table').removeClass('hidden');
				$('.analytics-realtime-sources tbody').html('');

				$.each(response.sources, function(k,v) {
					var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

					$('.analytics-realtime-sources tbody').append(row);
				});

				// countries

				$('.analytics-realtime-countries table').removeClass('hidden');
				$('.analytics-realtime-countries tbody').html('');

				$.each(response.countries, function(k,v) {
					var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

					$('.analytics-realtime-countries tbody').append(row);
				});

			} else {
				$('.no-active-visitors').removeClass('hidden');
				$('.analytics-realtime-content table').addClass('hidden');
				$('.analytics-realtime-sources table').addClass('hidden');
				$('.analytics-realtime-countries table').addClass('hidden');
			}
		}

		$('.analytics-widget-realtime').parent().parent().removeClass('loading');
	});

}
