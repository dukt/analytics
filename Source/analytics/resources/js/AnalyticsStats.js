var Analytics = {};
var googleVisualisationCalled = false;

Analytics.Stats = Garnish.Base.extend({
    data:{period:'week'},
    init: function(element, options)
    {
        console.log('Initializing Stats for', element);

        this.$element = $('#'+element);
        this.$spinner = $('.spinner', this.$element);
        this.$chart = $('.chart', this.$element);
        this.$settingsBtn = $('.dk-settings-btn', this.$element);

        this.chartRequest = options['cachedRequest'];
        this.chartResponse = options['cachedResponse'];

        this.addListener(this.$settingsBtn, 'click', 'openSettings');

        this.initGoogleVisualization($.proxy(function() {

            // Google Visualization is loaded and ready

            this.handleChartResponse('area', this.chartResponse);

        }, this));
    },

    initGoogleVisualization: function(onGoogleVisualizationLoaded)
    {
        if(googleVisualisationCalled == false)
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", { packages:['corechart', 'table', 'geochart'], 'language': AnalyticsChartLanguage });

            googleVisualisationCalled = true;
        }

        google.setOnLoadCallback($.proxy(function() {

            if(typeof(google.visualization) == 'undefined')
            {
                // No Internet ?

                // this.$widget.addClass('hidden');
                // this.$error.html('An unknown error occured');
                // this.$error.removeClass('hidden');

                return;
            }
            else
            {
                onGoogleVisualizationLoaded();
            }
        }, this));
    },

    openSettings: function(ev)
    {
        if(!this.settingsModal)
        {
            $form = $('<form class="settingsmodal modal"></form>').appendTo(Garnish.$bod);
            $body = $('<div class="body"/>').appendTo($form),
            $footer = $('<div class="footer"/>').appendTo($form),
            $buttons = $('<div class="buttons right"/>').appendTo($footer),
            $cancelBtn = $('<div class="btn">'+Craft.t('Cancel')+'</div>').appendTo($buttons),
            $saveBtn = $('<input type="submit" class="btn submit" value="'+Craft.t('Save')+'" />').appendTo($buttons);

            this.settingsModal = new Garnish.Modal($form, {
                visible: false,
                resizable: false
            });

            this.addListener($cancelBtn, 'click', function() {
                this.settingsModal.hide();
            });

            this.addListener($form, 'submit', $.proxy(function(ev) {

                ev.preventDefault();

                var data = $('input, textarea, select', $form).filter(':visible').serialize();
                var request = $.parseParams(data);

                this.chartResponse = this.sendRequest(request);

                this.settingsModal.hide();

            }, this));

            Craft.postActionRequest('analytics/settingsModal', {}, $.proxy(function(response, textStatus)
            {
                $('.body', this.settingsModal.$container).html(response.html);
                Craft.initUiElements();
            }, this));
        }
        else
        {
            this.settingsModal.show();
        }
    },

    sendRequest: function(data)
    {
        // data[csrfTokenName] = csrfTokenValue;

        this.$spinner.removeClass('hidden');
        this.$chart.addClass('hidden');

        Craft.postActionRequest('analytics/stats/getChart', data, $.proxy(function(response, textStatus)
        {
            this.$spinner.addClass('hidden');
            this.$chart.removeClass('hidden');
            this.handleChartResponse(data.chart, response);
        }, this));
    },

    handleChartResponse: function(chartType, response)
    {
        console.log('chartType, response', chartType, response);

        switch(chartType)
        {
            case "area":
                totalRows = response.area.rows.length;
                this.handleAreaChartResponse(response);
                break;

            case "counter":
                this.handleCounterResponse(response);
                break;

            default:
                console.error('Chart type "'+chartType+'" not supported.')
        }
    },

    handleAreaChartResponse: function(response)
    {
        // Data Table
        this.chartDataTable = Analytics.Utils.responseToDataTable(response.area);

        // Options
        this.chartOptions = Analytics.ChartOptions.area(response.period);

        if(response.period == 'year')
        {
            var dateFormatter = new google.visualization.DateFormat({
                pattern: "MMMM yyyy"
            });

            dateFormatter.format(this.chartDataTable, 0);
        }

        // Chart
        this.chart = new google.visualization.AreaChart(this.$chart.get(0));
        this.chart.draw(this.chartDataTable, this.chartOptions);
    },

    handleCounterResponse: function(response)
    {
        $counter = $('<div class="counter" />');
        $value = $('<div class="value" />').appendTo($counter),
        $label = $('<div class="label" />').appendTo($counter),
        $period = $('<div class="period" />').appendTo($counter);

        $value.html(response.counter.count);
        $label.html(response.metric);
        $period.html(response.period);

        this.$chart.html($counter);
    },

});



/**
 * AnalyticsUtils
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
 * $.parseParams - parse query string paramaters into an object.
 */
(function($) {
var re = /([^&=]+)=?([^&]*)/g;
var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};
$.parseParams = function(query) {
    var params = {}, e;
    while ( e = re.exec(query) ) {
        var k = decode( e[1] ), v = decode( e[2] );
        if (k.substring(k.length - 2) === '[]') {
            k = k.substring(0, k.length - 2);
            (params[k] || (params[k] = [])).push(v);
        }
        else params[k] = v;
    }
    return params;
};
})(jQuery);