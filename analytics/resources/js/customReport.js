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

            $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {


                $type = 'string';

                if(columnHeader.name == 'ga:date') {
                    $type = 'date';
                }
                else
                {
                    if(columnHeader.dataType == 'INTEGER')
                    {
                        $type = 'number';
                    }
                }

                chartData.addColumn($type, columnHeader.name);

            });

            $.each(response.apiResponse.rows, function(k, row) {

                $.each(response.apiResponse.columnHeaders, function(k2, columnHeader) {
                    if(k > -1)
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
                            if(columnHeader.dataType == 'INTEGER')
                            {
                                response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                            }
                        }

                    }
                    else
                    {
                        response.apiResponse.rows[k] = null;
                    }
                });


            });


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
                        height:"70%"
                    }
                };
                this.$chart = new google.visualization.ColumnChart(this.$chartElement.get(0));
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

