/**
 * Counter
 */
Analytics.reports.Counter = Analytics.reports.BaseChart.extend(
{
	initChart: function()
	{
		this.base();

		$value = $('<div class="value" />').appendTo(this.$graph),
			$label = $('<div class="label" />').appendTo(this.$graph),
			$period = $('<div class="period" />').appendTo(this.$graph);

		var value = Analytics.Utils.formatByType(this.data.counter.type, this.data.counter.value);

		$value.html(value);
		$label.html(this.data.metric);
		$period.html(' '+this.data.periodLabel);

		this.data.onAfterDraw();
	}
});