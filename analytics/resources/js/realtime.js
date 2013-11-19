$(document).ready(function() {

	console.log('init realtime');

	analyticsRealtimeRequest();

	setInterval(analyticsRealtimeRequest, 10000);
});

function analyticsRealtimeRequest()
{

	var data = {

	};

	console.log('send realtime request', data);

	Craft.postActionRequest('analytics/charts/realtime', data, function(response) {
		// realtime
		console.log('get realtime response', response);

		var newVisitor = response.visitorType.newVisitor;
		var returningVisitor = response.visitorType.returningVisitor;

		var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

		$('.analytics-realtime .active-visitors .count').text(calcTotal);

		if (calcTotal > 0) {
			$('.analytics-realtime .progress').removeClass('hidden');
			$('.analytics-realtime .legend').removeClass('hidden');
		} else {
			$('.analytics-realtime .progress').addClass('hidden');
			$('.analytics-realtime .legend').addClass('hidden');
		}


		if(calcTotal > 0) {
			var blue = Math.round(100 * newVisitor / calcTotal);
		} else {
			var blue = 100;
		}

		var green = 100 - blue;

		$('.analytics-realtime .progress-bar.blue').css('width', blue+'%');
		$('.analytics-realtime .progress-bar.green').css('width', green+'%');

		$('.analytics-realtime .progress-bar.blue span').text(blue+'%');
		$('.analytics-realtime .progress-bar.green span').text(green+'%');

		// realtime content


		if (calcTotal > 0) {
			$('.no-active-visitors').addClass('hidden');
			$('.analytics-realtime-content table').removeClass('hidden');

			$('.analytics-realtime-content tbody').html('');

			$.each(response.content, function(k,v) {
				var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

				$('.analytics-realtime-content tbody').append(row);
			});
		} else {
			$('.no-active-visitors').removeClass('hidden');
			$('.analytics-realtime-content table').addClass('hidden');
		}
	});

}
