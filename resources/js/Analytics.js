/**
 * Analytics
 */

 var Analytics = {
	GoogleVisualizationCalled: false,
	GoogleVisualizationReady: false,
	reports: {}
};

/**
 * Visualization
 */
Analytics.Visualization = Garnish.Base.extend({

	options: null,
	afterInitStack: [],

	init: function(options)
	{
		this.options = options;

		if(Analytics.GoogleVisualizationCalled == false)
		{
			Analytics.GoogleVisualizationCalled = true;

			if(typeof(AnalyticsChartLanguage) == 'undefined')
			{
				AnalyticsChartLanguage = 'en';
			}

			google.load("visualization", "1", {
				packages:['corechart', 'table'],
				language: AnalyticsChartLanguage,
				callback: $.proxy(function() {
					Analytics.GoogleVisualizationReady = true;

					this.onAfterInit();

					this.onAfterFirstInit();
				}, this)
			});
		}
		else
		{
			this.onAfterInit();
		}
	},

	onAfterFirstInit: function()
	{
		// call inAfterInits that are waiting for initialization completion

		for(i=0; i < this.afterInitStack.length; i++)
		{
			this.afterInitStack[i]();
		}
	},

	onAfterInit: function()
	{
		if(Analytics.GoogleVisualizationReady)
		{
			this.options.onAfterInit();
		}
		else
		{
			// add it to the stack
			this.afterInitStack.push(this.options.onAfterInit);
		}
	}
});

/**
 * Chart Options
 */
Analytics.ChartOptions = Garnish.Base.extend({}, {

	area: function(scale) {

		options = this.defaults.area;

		switch(scale)
		{
			case 'week':
			options.hAxis.format = 'E';
			options.hAxis.showTextEvery = 1;
			break;

			case 'month':
			options.hAxis.format = 'MMM d';
			options.hAxis.showTextEvery = 1;
			break;

			case 'year':
			options.hAxis.showTextEvery = 1;
			options.hAxis.format = 'MMM yy';
			break;
		}

		return options;
	},

	table: function()
	{
		return this.defaults.table;
	},

	geo: function(dimension)
	{
		options = this.defaults.geo;

		switch(dimension)
		{
			case 'ga:city':
			options.displayMode = 'markers';
			break;

			case 'ga:country':
			options.resolution = 'countries';
			options.displayMode = 'regions';
			break;

			case 'ga:continent':
			options.resolution = 'continents';
			options.displayMode = 'regions';
			break;

			case 'ga:subContinent':
			options.resolution = 'subcontinents';
			options.displayMode = 'regions';
			break;
		}

		return options;
	},

	pie: function()
	{
		return this.defaults.pie;
	},

	field: function()
	{
		return {
			theme: 'maximized',
			legend: 'none',
			backgroundColor: '#fdfdfd',
			colors: ['#058DC7'],
			areaOpacity: 0.1,
			pointSize: 0,
			lineWidth: 4,
			height: 120,
			hAxis: {
				//format:'MMM yy',
				format: 'MMM d',
				// format: 'E',
				textPosition: 'in',
				textStyle: {
					color: '#058DC7'
				},
				showTextEvery: 1,
				baselineColor: '#fff',
				gridlines: {
					color: 'none'
				}
			},
			vAxis: {
				textPosition: 'in',
				textStyle: {
					color: '#058DC7'
				},
				baselineColor: '#ccc',
				gridlines: {
					color: '#f4f4f4'
				},
				// viewWindow: {min:0, max: 10},
				minValue: 0,
				maxValue: 10,
				format: '#'
			}
		};
	},

	defaults: {
		area: {
			theme: 'maximized',
			legend: 'none',
			backgroundColor: '#FFF',
			colors: ['#058DC7'],
			areaOpacity: 0.1,
			pointSize: 7,
			lineWidth: 4,
			chartArea: {
			},
			hAxis: {
				//format:'MMM yy',
				// format: 'MMM d',
				format: 'E',
				textPosition: 'in',
				textStyle: {
					color: '#058DC7'
				},
				showTextEvery: 1,
				baselineColor: '#fff',
				gridlines: {
					color: 'none'
				}
			},
			vAxis: {
				textPosition: 'in',
				textStyle: {
					color: '#058DC7'
				},
				baselineColor: '#ccc',
				gridlines: {
					color: '#fafafa'
				},
				// viewWindow: {min:0, max: 10},
				minValue: 0,
				maxValue: 10,
				format: '#'
			}
		},

		geo: {
			// height: 282
			displayMode: 'auto'
		},

		pie: {
			theme: 'maximized',
			height: 282,
			pieHole: 0.5,
			legend: {
				alignment: 'center',
				position:'top'
			},
			chartArea:{
				top:40,
				height:'82%'
			},
			sliceVisibilityThreshold: 1/120
		},

		table: {
			// page: 'enable'
		}
	}
});


/**
 * Utils
 */
Analytics.Utils = {

	responseToDataTable: function(response)
	{
		var data = new google.visualization.DataTable();

		$.each(response.cols, function(k, column) {
			data.addColumn(column);
		});

		$.each(response.rows, function(kRow, row) {

			$.each(row, function(kCell, cell) {

				switch(response.cols[kCell]['type'])
				{
					case 'date':

						$dateString = cell.v;

						if($dateString.length == 8)
						{
							// 20150101

							$year = eval($dateString.substr(0, 4));
							$month = eval($dateString.substr(4, 2)) - 1;
							$day = eval($dateString.substr(6, 2));

							$date = new Date($year, $month, $day);

							row[kCell] = $date;
						}
						else if($dateString.length == 6)
						{
							// 201501

							$year = eval($dateString.substr(0, 4));
							$month = eval($dateString.substr(4, 2)) - 1;

							$date = new Date($year, $month, '01');

							row[kCell] = $date;
						}

						break;
				}
			});

			data.addRow(row);
		});

		return data;
	}
};