/**
 * Analytics
 */

 var Analytics = {
    GoogleVisualisationCalled: false
};


/**
 * Visualization
 */
Analytics.Visualization = Garnish.Base.extend({

    options: null,

    init: function(options)
    {
        this.options = options;

        if(Analytics.GoogleVisualisationCalled == false)
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", {
                packages:['corechart', 'table'],
                language: AnalyticsChartLanguage,
                callback: $.proxy(function() {
                    this.onAfterInit();
                }, this)
            });

            Analytics.GoogleVisualisationCalled = true;
        }
        else
        {
            this.onAfterInit();
        }
    },

    onAfterInit: function()
    {
        this.options.onAfterInit();
    }
});


/**
 * Chart
 */
Analytics.Chart = Garnish.Base.extend({

    type: null,
    chart: null,
    data: null,
    period: null,
    options: null,
    visualization: null,

    init: function($chart, data)
    {
        this.visualization = new Analytics.Visualization({
            onAfterInit: $.proxy(function() {

                this.$chart = $chart;
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

    initAreaChart: function()
    {
        this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);

        this.chartOptions = Analytics.ChartOptions.area(this.data.period);

        if(this.data.period == 'year')
        {
            var dateFormatter = new google.visualization.DateFormat({
                pattern: "MMMM yyyy"
            });

            dateFormatter.format(this.dataTable, 0);
        }

        this.chart = new google.visualization.AreaChart(this.$chart.get(0));

        this.draw();
    },

    initCounterChart: function()
    {
        $value = $('<div class="value" />').appendTo(this.$chart),
        $label = $('<div class="label" />').appendTo(this.$chart),
        $period = $('<div class="period" />').appendTo(this.$chart);

        $value.html(this.data.counter.count);
        $label.html(this.data.metric);
        $period.html(this.data.period);
    },

    initPieChart: function()
    {
        this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
        this.chartOptions = Analytics.ChartOptions.pie();
        this.chart = new google.visualization.PieChart(this.$chart.get(0));
        this.draw();
    },

    initTableChart: function()
    {
        this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
        this.chartOptions = Analytics.ChartOptions.table();
        this.chart = new google.visualization.Table(this.$chart.get(0));
        this.draw();
    },

    initGeoChart: function()
    {
        this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart);
        this.chartOptions = Analytics.ChartOptions.geo(this.data.dimensionRaw);
        this.chart = new google.visualization.GeoChart(this.$chart.get(0));
        this.draw();
    },

    initChart: function()
    {
        this.$chart.addClass(this.type);

        switch(this.type)
        {
            case "area":
                this.initAreaChart();
                break;

            case "counter":
                this.initCounterChart();
                break;

            case "geo":
                this.initGeoChart();
                break;

            case "pie":
                this.initPieChart();
                break;

            case "table":
                this.initTableChart();
                break;

            default:
                console.error('Chart type "'+this.type+'" not supported.')
        }
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
            colors: ['#058DC7'],
            backgroundColor: '#fdfdfd',
            areaOpacity: 0.1,
            pointSize: 8,
            lineWidth: 4,
            legend: false,
            hAxis: {
                textStyle: { color: '#888' },
                baselineColor: '#fdfdfd',
                gridlines: {
                    color: 'none',
                }
            },
            vAxis:{
                maxValue: 5,
            },
            series:{
                0:{targetAxisIndex:0},
                1:{targetAxisIndex:1}
            },
            vAxes: [
                {
                    textStyle: { color: '#888' },
                    format: '#',
                    textPosition: 'in',
                    baselineColor: '#eee',
                    gridlines: {
                        color: '#eee'
                    }
                },
                {
                    textStyle: { color: '#888' },
                    format: '#',
                    textPosition: 'in',
                    baselineColor: '#eee',
                    gridlines: {
                        color: '#eee'
                    }
                }
            ],
            chartArea:{
                top:10,
                bottom:10,
                width:"100%",
                height:"80%"
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
            pointSize: 8,
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
                maxValue: 0
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
        console.log('responseToDataTable', response);

        var data = new google.visualization.DataTable();

        $.each(response.cols, function(k, column) {
            data.addColumn(column);
        });

        console.log('response', response);

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
