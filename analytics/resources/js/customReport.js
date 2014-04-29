google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsCustomReport = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $("#"+element);
        this.$chartElement = $('.chart', this.$element);
        this.$totalsElement = $('.totals', this.$element);
        this.$chart = false;

        $id = this.$element.data('widget-id');

        var chartData = new google.visualization.DataTable();
        var options = {};

        Craft.postActionRequest('analytics/customReport', {id: $id}, function(response) {

            console.log(response.widget.settings.options.chartType, response);
            $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {


                $type = 'string';

                if(columnHeader.name == 'ga:date')
                {
                    $type = 'date';
                }
                else if(columnHeader.name == 'ga:latitude')
                {
                    $type = 'number';
                }
                else if(columnHeader.name == 'ga:longitude')
                {
                    $type = 'number';
                }
                else
                {
                    if(columnHeader.dataType == 'INTEGER')
                    {
                        $type = 'number';
                    }
                }

                if(response.widget.settings.options.chartType == 'PieChart' && k == 0)
                {
                    $type = 'string';
                }

                chartData.addColumn($type, columnHeader.name);

            });

            $.each(response.apiResponse.rows, function(k, row) {

                $.each(response.apiResponse.columnHeaders, function(k2, columnHeader) {

                    if(response.widget.settings.options.chartType == 'PieChart')
                    {
                        if(k2 == 0)
                        {
                            response.apiResponse.columnHeaders[k2].dataType = 'STRING';
                            response.apiResponse.rows[k][k2] = response.apiResponse.rows[k][k2];
                        }
                        else
                        {
                            if(columnHeader.dataType == 'INTEGER'
                                || columnHeader.name == 'ga:latitude'
                                || columnHeader.name == 'ga:longitude')
                            {
                                response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                            }
                            else if(columnHeader.name == 'ga:continent' || columnHeader.name == 'ga:subContinent')
                            {
                                response.apiResponse.rows[k][k2].v = ""+response.apiResponse.rows[k][k2].v;
                            }
                        }
                    }
                    else
                    {
                        if(columnHeader.name == 'ga:date')
                        {
                            $date = response.apiResponse.rows[k][k2];
                            $year = eval($date.substr(0, 4));
                            $month = eval($date.substr(4, 2)) - 1;
                            $day = eval($date.substr(6, 2));

                            newDate = new Date($year, $month, $day);

                            response.apiResponse.rows[k][k2] = newDate;
                        }
                        else
                        {
                            if(columnHeader.dataType == 'INTEGER'
                                || columnHeader.name == 'ga:latitude'
                                || columnHeader.name == 'ga:longitude')
                            {
                                response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                            }
                            else if(columnHeader.name == 'ga:continent' || columnHeader.name == 'ga:subContinent')
                            {
                                response.apiResponse.rows[k][k2].v = ""+response.apiResponse.rows[k][k2].v;
                            }
                        }
                    }
                });


            });

            console.log('rows', response.apiResponse.rows);

            chartData.addRows(response.apiResponse.rows);

            switch(response.widget.settings.options.chartType)
            {
                case "AreaChart":

                options = {
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
                            color: 'none'
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
                        width:"100%",
                        height:"70%"
                    }
                };

                this.$chart = new google.visualization.AreaChart(this.$chartElement.get(0));

                break;

                case "BarChart":
                this.$chart = new google.visualization.BarChart(this.$chartElement.get(0));
                break;

                case "ColumnChart":
                options = {
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
                        width:"100%",
                        height:"auto"
                    }
                };
                this.$chart = new google.visualization.ColumnChart(this.$chartElement.get(0));
                break;


                case 'GeoChart':
                console.log('region', response.widget.settings.options.region);

                options = {
                    region: response.widget.settings.options.region,
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
                    legend: {
                        alignment: 'center',
                        position:'top'
                    },
                    chartArea:{
                        top:40,
                        bottom:0,
                        width:"100%",
                        height:"80%"
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
                this.$chart.draw(chartData, options);
            }

            var $this = this;

            $(window).resize(function() {
                $this.$chart.draw(chartData, options);
            });

        }, this);

    },
});

