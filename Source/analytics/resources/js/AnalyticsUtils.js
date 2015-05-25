/**
 * AnalyticsUtils
 */
 var AnalyticsUtils = {

    responseToDataTable: function(response)
    {
        var data = new google.visualization.DataTable();

        $.each(response.cols, function(k, column) {
            data.addColumn(column);
        });


        $.each(response.rows, function(kRow, row) {
            $.each(row, function(kCell, cell) {
                switch(response.cols[kCell]['id'])
                {
                    case 'ga:date':

                        $dateString = cell.v;

                        $year = eval($dateString.substr(0, 4));
                        $month = eval($dateString.substr(4, 2)) - 1;
                        $day = eval($dateString.substr(6, 2));

                        $date = new Date($year, $month, $day);

                        row[kCell] = $date;

                        break;

                    case 'ga:yearMonth':

                        $dateString = cell.v;

                        $year = eval($dateString.substr(0, 4));
                        $month = eval($dateString.substr(4, 2)) - 1;

                        $date = new Date($year, $month, '01');

                        row[kCell] = $date;

                        break;
                }
            });

            data.addRow(row);
        });

        return data;
    },

    parseColumn: function(apiColumn)
    {
        $type = 'string';

        if(apiColumn.dataType == 'INTEGER'
            || apiColumn.dataType == 'FLOAT'
            || apiColumn.dataType == 'PERCENT'
            || apiColumn.dataType == 'CURRENCY'
            || apiColumn.dataType == 'TIME')
        {
            $type = 'number';
        }

        if(apiColumn.name == 'ga:date')
        {
            $type = 'date';
            apiColumn.dataType = 'DATE';
        }
        if(apiColumn.name == 'ga:latitude')
        {
            $type = 'number';
        }
        if(apiColumn.name == 'ga:longitude')
        {
            $type = 'number';
        }

        if(apiColumn.name == 'ga:yearMonth')
        {
            $type = 'date';
            apiColumn.dataType = 'DATE';
        }

        var column = {
            'type': $type,
            'dataType': apiColumn.dataType,
            'name': apiColumn.name,
            'label': apiColumn.label
        };

        return column;
    },

    parseRows: function(apiColumns, apiRows)
    {
        var rows = [];

        if (typeof(apiRows) == 'undefined')
        {
            return rows;
        };

        $.each(apiRows, function(k, row) {

            var cells = [];

            $.each(apiColumns, function(k2, column) {

                column = AnalyticsUtils.parseColumn(column);

                var cell = apiRows[k][k2];

                if(column.dataType == 'DATE')
                {
                    if(typeof(cell) == 'object')
                    {
                        $date = cell.v;
                    }
                    else
                    {
                        $date = cell;
                    }

                    $year = eval($date.substr(0, 4));
                    $month = eval($date.substr(5, 2)) - 1;
                    $day = eval($date.substr(8, 2));

                    newDate = new Date($year, $month, $day);

                    if(typeof($date) == 'object')
                    {
                        cell.v = newDate;
                        cell.f = 'x';
                    }
                    else
                    {
                        cell = newDate;
                    }
                }

                cells[k2] = cell;
            });

            rows[k] = cells;
        });

        return rows;
    },
};


/**
 * ChartOptions
 */
AnalyticsChartOptions = Garnish.Base.extend({}, {

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