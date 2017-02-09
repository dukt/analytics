/**
 * Field
 */
 AnalyticsReportField = Garnish.Base.extend({

	init: function(fieldId, options)
	{
		this.setSettings(options, AnalyticsReportField.defaults);

		this.$element = $("#"+fieldId);
		this.$field = $(".analytics-field", this.$element);
		this.$metric = $('.analytics-metric select', this.$element);
		this.$chart = $('.chart', this.$element);
		this.$spinner = $('.spinner', this.$element);
		this.$error = $('.error', this.$element);

		this.elementId = $('.analytics-field', this.$element).data('element-id');
		this.locale = $('.analytics-field', this.$element).data('locale');
		this.metric = this.$metric.val();

		this.addListener(this.$metric, 'change', 'onMetricChange');

		if(!this.settings.cachedResponse)
		{
			this.sendRequest();
		}
		else
		{
			this.parseResponse(this.settings.cachedResponse);
		}
	},

	onMetricChange: function(ev)
	{
		this.metric = $(ev.currentTarget).val();
		this.sendRequest();
	},

	sendRequest: function()
	{
		this.$spinner.removeClass('hidden');
		this.$field.removeClass('analytics-error');

		var data = {
			elementId: this.elementId,
			locale: this.locale,
			metric: this.metric
		};

		Craft.postActionRequest('analytics/reports/element', data, $.proxy(function(response, textStatus) {
			if(textStatus == 'success' && typeof(response.error) == 'undefined')
			{
				this.parseResponse(response);
			}
			else
			{
				var msg = Craft.t('An unknown error occurred.');

				if(typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined')
				{
					msg = response.error;
				}

				this.$error.html(msg);
				this.$error.removeClass('hidden');

				this.$field.addClass('analytics-error');
			}

			this.$spinner.addClass('hidden');

		}, this));
	},

	parseResponse: function(response)
	{
		Garnish.requestAnimationFrame($.proxy(function() {
            response.chartOptions = Analytics.ChartOptions.field();
			this.chart = new Analytics.reports.Area(this.$chart, response);
		}, this));
	}
}, {
	 defaults: {
		 cachedResponse: null,
	 }
 });