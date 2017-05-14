/**
 * Utils
 */
Analytics.Utils = {

    responseToDataTable: function(response)
    {
        var data = new google.visualization.DataTable();

        $.each(response.cols, function(k, column)
        {
            var type;

            switch(column.type)
            {
                case 'percent':
                case 'time':
                case 'integer':
                case 'currency':
                case 'float':
                    type = 'number';
                    break;


                case 'continent':
                case 'subContinent':
                    type = 'string';
                    break;

                default:
                    type = column.type;
            }

            data.addColumn({
                type: type,
                label: column.label,
                id: column.id,
            });
        });

        $.each(response.rows, function(kRow, row) {

            $.each(row, function(kCell, cell) {

                switch(response.cols[kCell]['type'])
                {
                    case 'continent':
                    case 'subContinent':
                    case 'currency':
                    case 'percent':
                    case 'integer':
                    case 'time':
                        row[kCell] = {
                            v: cell,
                            f: Analytics.Utils.formatByType(response.cols[kCell]['type'], cell)
                        };
                        break;

                    default:
                        row[kCell] = Analytics.Utils.formatByType(response.cols[kCell]['type'], cell);
                        break;
                }
            });

            data.addRow(row);
        });

        return data;
    },

    responseToDataTableV4: function(response)
    {
        var dataTable = new google.visualization.DataTable();


        // Columns

        $.each(response.columnHeader.dimensions, function(key, dimension) {
            var type;

            switch(dimension) {
                case 'ga:date':
                case 'ga:yearMonth':
                    type = 'date';
                    break;
                default:
                    type = 'string';
            }

            dataTable.addColumn({
                type: type,
                label: dimension,
                id: 'col-'+dimension,
            });
        });

        $.each(response.columnHeader.metricHeader, function(key, metricHeaderEntry) {
            var type;

            switch(metricHeaderEntry.type) {
                case 'INTEGER':
                case 'PERCENT':
                case 'TIME':
                    type = 'number';
                    break;
                default:
                    type = 'string';
            }
            dataTable.addColumn({
                type: type,
                label: metricHeaderEntry.name,
                id: 'col-'+metricHeaderEntry.name,
            });
        });


        // Rows

        $.each(response.data.rows, function(keyRow, row) {

            var dataTableRow = [];
            var dataTableRowIndex = 0;

            $.each(response.columnHeader.dimensions, function(key, dimension) {
                var value;

                switch(dimension) {
                    case 'ga:date':
                    case 'ga:yearMonth':
                        value = Analytics.Utils.formatByType('date', row.dimensions[key]);
                        break;
                    default:
                        value = row.dimensions[key];
                }

                dataTableRow[dataTableRowIndex] = value;
                dataTableRowIndex++;
            });

            $.each(response.columnHeader.metricHeader, function(key, metricHeaderEntry) {
                var value = row.metrics[key].values[0];

                switch(metricHeaderEntry.type) {
                    case 'INTEGER':
                        dataTableRow[dataTableRowIndex] = +value;
                        break;
                    case 'PERCENT':
                        dataTableRow[dataTableRowIndex] = {
                            v: +value,
                            f: Analytics.Utils.formatByType('percent', +value)
                        };
                        break;
                    case 'TIME':
                        dataTableRow[dataTableRowIndex] = {
                            v: +value,
                            f: Analytics.Utils.formatByType('time', +value)
                        };
                        break;
                    default:
                        dataTableRow[dataTableRowIndex] = value;
                }

                dataTableRowIndex++;
            });

            dataTable.addRow(dataTableRow);
        });

        return dataTable;
    },

    formatByType: function(type, value)
    {
        switch (type)
        {
            case 'continent':
                return Analytics.Metadata.getContinentByCode(value);
                break;
            case 'subContinent':
                return Analytics.Metadata.getSubContinentByCode(value);
                break;
            case 'currency':
                return Analytics.Utils.formatCurrency(value);
                break;

            case 'integer':
                return Analytics.Utils.formatInteger(value);
                break;

            case 'time':
                return Analytics.Utils.formatDuration(value);
                break;

            case 'percent':
                return Analytics.Utils.formatPercent(value);
                break;

            case 'date':
                $dateString = value;

                if($dateString.length == 8)
                {
                    // 20150101

                    $year = eval($dateString.substr(0, 4));
                    $month = eval($dateString.substr(4, 2)) - 1;
                    $day = eval($dateString.substr(6, 2));

                    $date = new Date($year, $month, $day);

                    return $date;
                }
                else if($dateString.length == 6)
                {
                    // 201501

                    $year = eval($dateString.substr(0, 4));
                    $month = eval($dateString.substr(4, 2)) - 1;

                    $date = new Date($year, $month, '01');

                    return $date;
                }
                break;

            default:
                return value;
                break;
        }
    },

    formatCurrency: function(value)
    {
        return this.getD3Locale().format(Craft.charts.BaseChart.defaults.formats.currencyFormat)(value);
    },

    formatDuration: function(_seconds)
    {
        var sec_num = parseInt(_seconds, 10); // don't forget the second param
        var hours   = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours   < 10) {hours   = "0"+hours;}
        if (minutes < 10) {minutes = "0"+minutes;}
        if (seconds < 10) {seconds = "0"+seconds;}
        return hours+':'+minutes+':'+seconds;
    },

    formatInteger: function(value)
    {
        return this.getD3Locale().format(",")(value);
    },

    formatPercent: function(value)
    {
        return this.getD3Locale().format(Craft.charts.BaseChart.defaults.formats.percentFormat)(value / 100);
    },

    getD3Locale: function()
    {
        /*
        this.formatLocale = d3.formatLocale(this.settings.formatLocaleDefinition);
        this.timeFormatLocale = d3.timeFormatLocale(this.settings.timeFormatLocaleDefinition);
        */

        var localeDefinition = window['d3FormatLocaleDefinition'];

        localeDefinition.currency = Analytics.currency;

        return d3.formatLocale(localeDefinition);
    },
};
