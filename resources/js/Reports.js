
/**
 * BaseChart
 */
Analytics.reports.BaseChart = Garnish.Base.extend(
{
	$chart: null,
	$graph: null,

	type: null,
	chart: null,
	chartOptions: null,
	data: null,
	period: null,
	options: null,
	visualization: null,

	init: function($element, data)
	{
		this.visualization = new Analytics.Visualization({
			onAfterInit: $.proxy(function() {

				this.$chart = $element;
				this.$chart.html('');
				this.$graph = $('<div class="chart" />').appendTo(this.$chart);

				this.data = data;

				if(typeof(this.data.chartOptions) != 'undefined')
				{
					this.chartOptions = this.data.chartOptions;
				}

				if(typeof(this.data.type) != 'undefined')
				{
					this.type = this.data.type;
				}

				if(typeof(this.data.period) != 'undefined')
				{
					this.period = this.data.period;
				}

				this.addListener(Garnish.$win, 'resize', 'resize');

				this.initChart();

				if(typeof(this.data.onAfterInit) != 'undefined')
				{
					this.data.onAfterInit();
				}
			}, this)
		});
	},

	initChart: function()
	{
		this.$graph.addClass(this.type);
		// console.error('Chart type "'+this.type+'" not supported.')
	},

	draw: function()
	{
		if(this.dataTable && this.chartOptions)
		{
			this.chart.draw(this.dataTable, this.chartOptions);
		}
	},

	resize: function()
	{
		if(this.chart && this.dataTable && this.chartOptions)
		{
			this.chart.draw(this.dataTable, this.chartOptions);
		}
	},
});

/**
 * Area
 */
Analytics.reports.Area = Analytics.reports.BaseChart.extend(
{
	initChart: function()
	{
		this.base();

		$period = $('<div class="period" />').prependTo(this.$chart);
		$title = $('<div class="title" />').prependTo(this.$chart);
		$title.html(this.data.metric);
		$period.html(this.data.periodLabel);

		this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
		this.chartOptions = Analytics.ChartOptions.area(this.data.period);

		if(typeof(this.data.chartOptions) != 'undefined')
		{
			$.extend(this.chartOptions, this.data.chartOptions);
		}

		if(this.data.period == 'year')
		{
			var dateFormatter = new google.visualization.DateFormat({
				pattern: "MMMM yyyy"
			});

			dateFormatter.format(this.dataTable, 0);
		}

		this.chart = new google.visualization.AreaChart(this.$graph.get(0));

		this.draw();
	}
});

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

		$value.html(this.data.counter.count);
		$label.html(this.data.metric);
		$period.html(' '+this.data.periodLabel);
	}
});

/**
 * Pie
 */
Analytics.reports.Pie = Analytics.reports.BaseChart.extend(
{
	initChart: function()
	{
		this.base();

		$period = $('<div class="period" />').prependTo(this.$chart);
		$title = $('<div class="title" />').prependTo(this.$chart);
		$title.html(this.data.dimension);
		$period.html(this.data.metric+" "+this.data.periodLabel);

		this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
		this.chartOptions = Analytics.ChartOptions.pie();
		this.chart = new google.visualization.PieChart(this.$graph.get(0));
		this.draw();
	}
});

/**
 * Table
 */
Analytics.reports.Table = Analytics.reports.BaseChart.extend(
{
	initChart: function()
	{
		this.base();

		$period = $('<div class="period" />').prependTo(this.$chart);
		$title = $('<div class="title" />').prependTo(this.$chart);
		$title.html(this.data.metric);
		$period.html(this.data.periodLabel);

		this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
		this.chartOptions = Analytics.ChartOptions.table();
		this.chart = new google.visualization.Table(this.$graph.get(0));
		this.draw();
	}
});

/**
 * Geo
 */
Analytics.reports.Geo = Analytics.reports.BaseChart.extend(
{
	initChart: function()
	{
		this.base();

		$period = $('<div class="period" />').prependTo(this.$chart);
		$title = $('<div class="title" />').prependTo(this.$chart);
		$title.html(this.data.metric);
		$period.html(this.data.periodLabel);

		this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
		this.chartOptions = Analytics.ChartOptions.geo(this.data.dimensionRaw);
		this.chart = new google.visualization.GeoChart(this.$graph.get(0));
		this.draw();
	}
});
