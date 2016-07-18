/**
 * Realtime
 */
Analytics.Realtime = Garnish.Base.extend(
{
	$element: null,
	$title: null,
	$body: null,
	$spinner: null,
	$streamstatus: null,
	$error: null,
	$activeVisitorsCount: null,
	$progress: null,
	$legend: null,
	$realtimeVisitors: null,
	$newVisitorsProgress: null,
	$newVisitorsValue: null,
	$returningVisitorsProgress: null,
	$returningVisitorsValue: null,

	timer: null,

	init: function(element)
	{
		this.$element = $('#'+element);
		this.$title = $('.title', this.$element);
		this.$body = $('.body', this.$element);
		this.$spinner = $('.spinner', this.$element);
		this.$streamstatus = $('.streamstatus', this.$element);
		this.$error = $('.error', this.$element);

		this.$activeVisitorsCount = $('.active-visitors .count', this.$realtimeVisitors);

		this.$progress = $('.progress', this.$realtimeVisitors);
		this.$legend = $('.legend', this.$realtimeVisitors);

		this.$realtimeVisitors = $('.analytics-realtime-visitors', this.$element);

		this.$newVisitorsProgress = $('.progress-bar.new-visitors', this.$realtimeVisitors);
		this.$newVisitorsValue = $('.progress-bar.new-visitors span', this.$realtimeVisitors);
		this.$returningVisitorsProgress = $('.progress-bar.returning-visitors', this.$realtimeVisitors);
		this.$returningVisitorsValue = $('.progress-bar.returning-visitors span', this.$realtimeVisitors);

		this.timer = false;

		this.start();

		setInterval($.proxy(function()
		{
			if(this.$streamstatus.hasClass('hidden'))
			{
				this.$streamstatus.removeClass('hidden');
			}
			else
			{
				this.$streamstatus.addClass('hidden');
			}

		}, this), 1000);
	},

	start: function()
	{
		if(this.timer)
		{
			this.stop();
		}

		this.request();

		this.timer = setInterval($.proxy(function()
		{
			this.request();

		}, this), AnalyticsRealtimeInterval * 1000);
	},

	stop: function()
	{
		clearInterval(this.timer);
	},

	request: function()
	{
		this.$spinner.removeClass('body-loading');
		this.$spinner.removeClass('hidden');

		Craft.queueActionRequest('analytics/reports/getRealtimeReport', {}, $.proxy(function(response, textStatus)
		{
			if(textStatus == 'success' && typeof(response.error) == 'undefined')
			{
				this.$error.addClass('hidden');
				this.$realtimeVisitors.removeClass('hidden');
				this.handleResponse(response);
			}
			else
			{
				var msg = 'An unknown error occured.';

				if(typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined')
				{
					msg = response.error;
				}

				this.$realtimeVisitors.addClass('hidden');
				this.$error.html(msg);
				this.$error.removeClass('hidden');
			}

			this.$spinner.addClass('hidden');

		}, this));
	},

	handleResponse: function(response)
	{
		var newVisitor = response.newVisitor;
		var returningVisitor = response.returningVisitor;

		var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

		this.$activeVisitorsCount.text(calcTotal);

		if (calcTotal > 0)
		{
			this.$progress.removeClass('hidden');
			this.$legend.removeClass('hidden');
		}
		else
		{
			this.$progress.addClass('hidden');
			this.$legend.addClass('hidden');
		}

		if(calcTotal > 0)
		{
			var calcNewVisitor = Math.round(100 * newVisitor / calcTotal);
		}
		else
		{
			var calcNewVisitor = 100;
		}

		var calcReturningVisitor = 100 - calcNewVisitor;


		// new-visitor

		this.$newVisitorsProgress.css('width', calcNewVisitor+'%');
		this.$newVisitorsValue.text(calcNewVisitor+'%');

		if(calcNewVisitor > 0)
		{
			this.$newVisitorsProgress.removeClass('hidden');
		}
		else
		{
			this.$newVisitorsProgress.addClass('hidden');
		}


		// returning-visitor

		this.$returningVisitorsProgress.css('width', calcReturningVisitor+'%');
		this.$returningVisitorsValue.text(calcReturningVisitor+'%');

		if(calcReturningVisitor > 0)
		{
			this.$returningVisitorsProgress.removeClass('hidden');
		}
		else
		{
			this.$returningVisitorsProgress.addClass('hidden');
		}
	},
});
