google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsCustomReport = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $("#"+element);
        this.$chartElement = $('.chart', this.$element);
        this.$errorElement = $('.error', this.$element);
        this.$totalsElement = $('.totals', this.$element);
        this.$chart = false;

        $id = this.$element.data('widget-id');

        var chartData = new google.visualization.DataTable();
        var options = {};

        Craft.queueActionRequest('analytics/customReport', {id: $id}, $.proxy(function(response) {
            if(typeof(response.error) != 'undefined')
            {
                this.$errorElement.html(response.error);
                this.$element.addClass('error');
            }
            else
            {
                var columns = AnalyticsUtils.getColumns(response);
                $.each(columns, function(k, column) {
                    chartData.addColumn(column.type, column.name);
                });

                var rows = AnalyticsUtils.getRows(response);
                chartData.addRows(rows);

                switch(response.widget.settings.options.chartType)
                {
                    case "AreaChart":

                    options = {
                        theme: 'maximized',
                        colors: ['#058DC7'],
                        areaOpacity: 0.1,
                        pointSize: 8,
                        lineWidth: 4,
                        hAxis: {
                            baselineColor: '#fff',
                            gridlines: {
                                color: 'none'
                            }
                        },
                        vAxis: {
                            baselineColor: '#ccc',
                            gridlines: {
                                color: '#eee'
                            }
                        }
                    };

                    this.$chart = new google.visualization.AreaChart(this.$chartElement.get(0));

                    break;

                    case "BarChart":
                    this.$chart = new google.visualization.BarChart(this.$chartElement.get(0));
                    break;

                    case "ColumnChart":
                    options = {
                        colors: ['#058DC7'],
                        areaOpacity: 0.1,
                        pointSize: 8,
                        lineWidth: 4,
                        legend: {
                            alignment: 'automatic',
                            position:'top',
                            maxLines:4
                        },
                        hAxis: {
                            baselineColor: '#fff',
                            gridlines: {
                                count:0
                            }
                        },
                        vAxis: {
                            textPosition: 'in',
                            baselineColor: '#ccc',
                            gridlines: {
                                color: '#eee'
                            }
                        },
                        chartArea:{
                            top:40,
                            bottom:0,
                            width:"100%"
                        }
                    };
                    this.$chart = new google.visualization.ColumnChart(this.$chartElement.get(0));
                    break;


                    case 'GeoChart':
                    // console.log('region', response.widget.settings.options.region);

                    options = {
                        theme: 'maximized',
                        colors: ['#058DC7'],
                        region: response.widget.settings.options.region
                    };

                    if(response.widget.settings.options.dimension == 'ga:continent')
                    {
                        options.resolution = 'continents';
                        options.displayMode = 'regions';
                    }
                    else if(response.widget.settings.options.dimension == 'ga:subContinent')
                    {
                        options.resolution = 'subcontinents';
                        options.displayMode = 'regions';
                    }
                    else if(response.widget.settings.options.dimension == 'ga:region')
                    {
                        options.resolution = 'provinces';
                        //options.displayMode = 'regions';
                    }
                    else if(response.widget.settings.options.dimension == 'ga:metro')
                    {
                        options.resolution = 'metros';
                        //options.displayMode = 'regions';
                    }

                    this.$chart = new google.visualization.GeoChart(this.$chartElement.get(0));
                    break;

                    case "PieChart":

                    options = {
                        theme: 'maximized',
                        pieHole: 0.5,
                        legend: {
                            alignment: 'center',
                            position:'top'
                        },
                        chartArea:{
                            top:40,
                            height:'82%'
                        },
                        sliceVisibilityThreshold: 0
                    };

                    this.$chart = new google.visualization.PieChart(this.$chartElement.get(0));
                    break;

                    case "Table":
                    this.$chart = new google.visualization.Table(this.$chartElement.get(0));
                    break;

                }

                if(typeof(this.$chart) != 'undefined')
                {
                    // console.log('options', options);
                    this.$chart.draw(chartData, options);
                }

                var $this = this;

                $(window).resize(function() {
                    $this.$chart.draw(chartData, options);
                });
            }


        }, this));

    },
});

