var AnalyticsUtils = {

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