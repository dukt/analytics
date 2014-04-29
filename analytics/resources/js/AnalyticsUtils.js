var AnalyticsUtils = {
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

            console.log('header', $type, columnHeader.name);

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