var Analytics = {};
var googleVisualisationCalled = false;

Analytics.Stats = Garnish.Base.extend({
    requestData: null,
    init: function(element, options)
    {
        console.log('Analytics.Stats(element, options)');
        console.log('---- element', element);
        console.log('---- options', options);


        // elements

        this.$element = $('#'+element);
        this.$title = $('.title', this.$element);
        this.$body = $('.body', this.$element);
        this.$date = $('.date', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$settingsBtn = $('.dk-settings-btn', this.$element);


        // default/cached request and response

        this.chartRequest = options['cachedRequest'];
        this.chartResponse = options['cachedResponse'];

        if(typeof(this.chartRequest) != 'undefined')
        {
            this.requestData = this.chartRequest;
        }


        // listeners

        this.addListener(this.$settingsBtn, 'click', 'openSettings');

        // initialize Google Visualization

        this.initGoogleVisualization($.proxy(function() {

            // Google Visualization is loaded and ready

            if(this.chartResponse)
            {
                this.$spinner.addClass('hidden');
                this.handleChartResponse(this.requestData.chart, this.chartResponse);
            }
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

    periodChange: function(ev)
    {
        console.log('Analytics.Stats.periodChange()');

        if(this.requestData)
        {
            this.requestData.period = $(ev.currentTarget).val();

            this.chartResponse = this.sendRequest(this.requestData);
        }
    },

    openSettings: function(ev)
    {
        if(!this.settingsModal)
        {
            $form = $('<form class="settingsmodal modal fitted"></form>').appendTo(Garnish.$bod);
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

                var stringData = $('input, textarea, select', $form).filter(':visible').serializeJSON();

                this.requestData = stringData;

                console.log('parsedParams', this.requestData);

                // this.$element.parents('.item').data('colspan', this.requestData.colspan);

                this.chartResponse = this.sendRequest(this.requestData);

                this.settingsModal.hide();

                this.saveState();

                // Craft.initUiElements();
                // Garnish.$win.trigger('resize');

            }, this));

            Craft.postActionRequest('analytics/settingsModal', { id: this.$element.data('id') }, $.proxy(function(response, textStatus)
            {
                $('.body', this.settingsModal.$container).html(response.html);

                this.$periodSelect = $('.period select', this.settingsModal.$container);
                this.$chartSelect = $('.chart-select select', this.settingsModal.$container);

                if(this.requestData)
                {
                    this.$chartSelect.val(this.requestData.chart);
                    this.$chartSelect.trigger('change');

                    this.$periodSelect.val(this.requestData.period);
                    this.$periodSelect.trigger('change');
                }

                Craft.initUiElements();

            }, this));
        }
        else
        {
            this.settingsModal.show();
        }
    },

    saveState: function()
    {
        console.log('Analytics.Stats().saveState()');

        var data = {
            id: this.$element.data('id'),
            settings: {
                colspan: this.requestData['colspan'],
                chart: this.requestData['chart'],
                period: this.requestData['period'],
                options: this.requestData['options'],
            }
        };

        console.log('Save state data', data);

        Craft.queueActionRequest('analytics/saveWidgetState', data, $.proxy(function(response)
        {
            // state saved

        }, this));
    },

    sendRequest: function(data)
    {
        // data[csrfTokenName] = csrfTokenValue;
        //data.period = this.$period.val();

        this.$spinner.removeClass('hidden');

        $('.chart', this.$body).remove();

        console.log('Analytics.Stats().sendRequest(data)');
        console.log('---- data', data);

        Craft.postActionRequest('analytics/stats/getChart', data, $.proxy(function(response, textStatus)
        {
            this.$spinner.addClass('hidden');
            this.handleChartResponse(data.chart, response);
        }, this));
    },

    handleChartResponse: function(chartType, response)
    {
        switch(chartType)
        {
            case "area":
                this.handleAreaChartResponse(response);
                break;

            case "counter":
                this.handleCounterResponse(response);
                break;

            case "geo":
                this.handleGeoResponse(response);
                break;

            case "pie":
                this.handlePieResponse(response);
                break;

            case "table":
                this.handleTableResponse(response);
                break;

            default:
                console.error('Chart type "'+chartType+'" not supported.')
        }
    },

    handleGeoResponse: function(response)
    {
        $chart = $('<div class="chart geo" />');
        $chart.appendTo(this.$body);

        this.chartDataTable = Analytics.Utils.responseToDataTable(response.table);
        this.chartOptions = Analytics.ChartOptions.geo(this.requestData.dimensions);
        this.chart = new google.visualization.GeoChart($chart.get(0));
        this.chart.draw(this.chartDataTable, this.chartOptions);
    },

    handleTableResponse: function(response)
    {
        $chart = $('<div class="chart table" />');
        $chart.appendTo(this.$body);

        this.chartDataTable = Analytics.Utils.responseToDataTable(response.table);
        this.chartOptions = Analytics.ChartOptions.table();
        this.chart = new google.visualization.Table($chart.get(0));
        this.chart.draw(this.chartDataTable, this.chartOptions);
    },

    handlePieResponse: function(response)
    {
        $chart = $('<div class="chart pie" />');
        $chart.appendTo(this.$body);

        this.chartDataTable = Analytics.Utils.responseToDataTable(response.chart);
        this.chartOptions = Analytics.ChartOptions.pie();
        this.chart = new google.visualization.PieChart($chart.get(0));
        this.chart.draw(this.chartDataTable, this.chartOptions);
    },

    handleAreaChartResponse: function(response)
    {
        $chart = $('<div class="chart area" />');
        $chart.appendTo(this.$body);

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
        this.chart = new google.visualization.AreaChart($chart.get(0));
        this.chart.draw(this.chartDataTable, this.chartOptions);

        this.$title.html(response.metric);
        this.$date.html(response.periodLabel);
    },

    handleCounterResponse: function(response)
    {
        $chart = $('<div class="chart counter" />').appendTo(this.$body);
        $value = $('<div class="value" />').appendTo($chart),
        $label = $('<div class="label" />').appendTo($chart),
        $period = $('<div class="period" />').appendTo($chart);

        $value.html(response.counter.count);
        $label.html(response.metric);
        $period.html(response.period);
    },
});


/**
 * AnalyticsUtils
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
