

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

            if(this.chartRequest)
            {
                if(this.chartResponse)
                {
                    this.$spinner.addClass('hidden');
                    this.handleChartResponse(this.requestData.chart, this.chartResponse);
                }
                else
                {
                    this.chartResponse = this.sendRequest(this.requestData);
                }
            }
        }, this));
    },

    initGoogleVisualization: function(onGoogleVisualizationLoaded)
    {
        if(Analytics.GoogleVisualisationCalled == false)
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", { packages:['corechart', 'table', 'geochart'], 'language': AnalyticsChartLanguage });

            Analytics.GoogleVisualisationCalled = true;
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

        Craft.postActionRequest('analytics/reports/getChartReport', data, $.proxy(function(response, textStatus)
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
