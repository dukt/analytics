var AnalyticsUtils = {
    getChart: function(chartElement, chartType, opts)
    {

        $chart = null;
        $chartOptions = {};

        switch(chartType)
        {
            case "AreaChart":

            $chartOptions = {
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

            $chart = new google.visualization.AreaChart(chartElement);

            break;

            case "BarChart":
            $chart = new google.visualization.BarChart(chartElement);
            break;

            case "ColumnChart":
            $chartOptions = {
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
            $chart = new google.visualization.ColumnChart(chartElement);
            break;


            case 'GeoChart':

            region = 'world';
            resolution = null;
            displayMode = null;

            if(typeof(opts) != 'undefined')
            {
                if(typeof(opts.region) != 'undefined')
                {
                    region = opts.region;
                }

                if(typeof(opts.resolution) != 'undefined')
                {
                    resolution = opts.resolution;
                }

                if(typeof(opts.displayMode) != 'undefined')
                {
                    displayMode = opts.displayMode;
                }
            }


            $chartOptions = {
                theme: 'maximized',
                colors: ['#058DC7'],
                region: region,
                resolution: resolution,
                displayMode: displayMode
            };

            // if(opts.dimension == 'ga:continent')
            // {
            //     $chartOptions.resolution = 'continents';
            //     $chartOptions.displayMode = 'regions';
            // }
            // else if(opts.dimension == 'ga:subContinent')
            // {
            //     $chartOptions.resolution = 'subcontinents';
            //     $chartOptions.displayMode = 'regions';
            // }
            // else if(opts.dimension == 'ga:region')
            // {
            //     $chartOptions.resolution = 'provinces';
            // }
            // else if(opts.dimension == 'ga:metro')
            // {
            //     $chartOptions.resolution = 'metros';
            // }

            $chart = new google.visualization.GeoChart(chartElement);
            break;

            case "PieChart":

            $chartOptions = {
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

            $chart = new google.visualization.PieChart(chartElement);
            break;

            case "Table":

            page = 'disable';

            if(typeof(opts) != 'undefined')
            {
                if(typeof(opts.page) != 'undefined')
                {
                    page = opts.page;
                }
            }

            $chartOptions = {
                page: page
            };

            $chart = new google.visualization.Table(chartElement);
            break;
        }


        return {
            'chartOptions' : $chartOptions,
            'chart': $chart
        };
    },
    getColumns: function(response)
    {
        var columns = [];

        $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {

            $type = 'string';

            if(columnHeader.name == 'ga:date') {
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
                if(columnHeader.dataType == 'INTEGER'
                    || columnHeader.dataType == 'PERCENT'
                    || columnHeader.dataType == 'TIME')
                {
                    $type = 'number';
                }
            }

            if(typeof(response.widget) != "undefined")
            {

                if(response.widget.settings.options.chartType == 'PieChart' && k == 0)
                {
                    $type = 'string';
                }
            }

            // console.log('header', $type, columnHeader.name);

            columns[k] = {
                'type': $type,
                'dataType': columnHeader.dataType,
                'name': columnHeader.name
            };
        });

        return columns;
    },

    getRows: function(response)
    {
        var rows = [];
        var columns = this.getColumns(response);

        $.each(response.apiResponse.rows, function(k, row) {

            var cells = [];

            $.each(columns, function(k2, column) {

                var cell = response.apiResponse.rows[k][k2];

                if(column.type == 'date')
                {
                    $date = cell;
                    $year = eval($date.substr(0, 4));
                    $month = eval($date.substr(4, 2)) - 1;
                    $day = eval($date.substr(6, 2));

                    newDate = new Date($year, $month, $day);

                    cell = newDate;
                }
                else
                {
                    if(column.dataType == 'INTEGER'
                        || column.name == 'ga:latitude'
                        || column.name == 'ga:longitude')
                    {
                        cell = eval(cell);
                    }
                    else if(column.dataType == 'PERCENT')
                    {
                        cell = {
                            'f': (Math.round(eval(cell) * 100) / 100)+" %",
                            'v': eval(cell)
                        };
                    }
                    else if(column.dataType == 'TIME')
                    {
                        cell = {
                            'f' : eval(cell)+" seconds",
                            'v' : eval(cell),
                        };
                    }
                    else if(column.name == 'ga:continent' || column.name == 'ga:subContinent')
                    {
                        cell.v = ""+cell.v;
                    }
                }

                cells[k2] = cell;
            });

            rows[k] = cells;
        });

        return rows;
    },
};