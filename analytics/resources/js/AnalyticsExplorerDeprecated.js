


/**
 * Explorer
 */
Analytics.ExplorerDeprecated = Garnish.Base.extend({

    /*

    Explorer
        Menu (view selector)
        Pin/Unpin
        Open

        Views
            Realtime Visitors
            Browser (realtime/not realtime)
                chartType
                period
                dimension
                metric
    */


    init: function(element, settings)
    {
        this.$element = $('#'+element);
        this.$error = $('.analytics-error', this.$element);
        this.$widget = $('.analytics-widget:first', this.$element);

        this.realtimeVisitorsView = new Analytics.RealtimeVisitorsView();
        this.browserView = new Analytics.BrowserView();

        // google visualization

        if(googleVisualisationCalled == false)
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", {packages:['corechart', 'table', 'geochart'], 'language': AnalyticsChartLanguage});

            googleVisualisationCalled = true;

        }

        google.setOnLoadCallback($.proxy(function() {

            if(typeof(google.visualization) == 'undefined')
            {
                this.$widget.addClass('hidden');
                this.$error.html('An unknown error occured');
                this.$error.removeClass('hidden');
                return;
            }
            else
            {
                this.initWidget(element, settings);
            }
        }, this));
    },

    initWidget: function(element, settings)
    {
        // variables

        this.timer = false;
        this.requestData = false;

        this.currentMenu = 'audienceOverview';
        this.currentChart = 'area';
        this.currentMetric = 'ga:sessions';
        this.currentPeriod = 'month';
        this.pinned = 0;

        this.singleChart = false;
        this.singleChartData = false;
        this.singleChartOptions = false;


        // elements

        this.$menu = $('.analytics-menu:first select:first', this.$element);
        this.$browser = $('.analytics-browser:first', this.$element);
        this.$dimensionField = $('.analytics-dimension-field', this.$element);
        this.$dimension = $('.analytics-dimension select', this.$element);
        this.$metric = $('.analytics-metric select', this.$element);
        this.$tableType = $('.analytics-tabletype:first', this.$element);
        this.$disabledTableType = $('.analytics-disabled-tabletype:first', this.$element);
        this.$tableTypeBtns = $('.analytics-tabletype .btn', this.$element);
        this.$period = $('.analytics-period', this.$element);
        this.$periodSelect = $('.analytics-period select', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$pin = $('.analytics-pin', this.$element);
        this.$infosDimension = $('.analytics-infos-dimension', this.$element);
        this.$infosMetric = $('.analytics-infos-metric', this.$element);
        this.$infosPeriod = $('.analytics-infos-period', this.$element);
        this.$infosCount = $('.analytics-infos-count', this.$element);
        this.$counter = $('.analytics-counter', this.$element);
        this.$counterValue = $('.analytics-counter-value', this.$element);
        this.$counterLabel = $('.analytics-counter-label', this.$element);
        this.$counterPeriod = $('.analytics-counter-period', this.$element);
        this.$collapsible = $('.analytics-collapsible', this.$element);
        this.$open = $('.analytics-open', this.$element);
        this.$realtimeVisitors = $('.analytics-realtime-visitors', this.$element);
        this.$singleChart = $('.analytics-single-chart', this.$element);


        // listeners
        this.addListener(this.$menu, 'change', 'onMenuChange');
        this.addListener(this.$tableTypeBtns, 'click', 'onTableTypeChange');
        this.addListener(this.$open, 'click', 'onOpen');
        this.addListener(this.$periodSelect, 'change', 'onPeriodChange');
        this.addListener(this.$pin, 'click', 'onPin');
        this.addListener(this.$dimension, 'change', 'onChangeDimension');
        this.addListener(this.$metric, 'change', 'onChangeMetric');
        this.addListener(Garnish.$win, 'resize', 'resize');

        // settings
        this.applySettings(settings);

        this.$spinner.removeClass('body-loading');
        this.$periodSelect.val(this.currentPeriod);


        // pin

        if(this.pinned)
        {
            this.$pin.addClass('active');

            this.$collapsible.animate({
                opacity: 0,
                height: "toggle"
            }, 0);
        }

        // default values
        this.$menu.val(this.currentMenu);
        this.$dimension.val(this.currentDimension);
        this.$metric.val(this.currentMetric);
        this.$period.val(this.currentPeriod);
        this.changeMenu(this.currentMenu);
        this.changeTableType(this.currentChart);

        // browse
        this.browse();
    },


    onMenuChange: function(ev)
    {
        $value = $(ev.currentTarget).val();
        this.currentMenu = $value;
        this.currentNav = $value;

        this.changeMenu($value);
        this.browse();
        this.saveState();
    },

    changeMenu: function(currentNav)
    {
        this.currentNav = currentNav;

        // update selects
        this.$dimension.html('');
        this.$metric.html('');

        // set section
        this.setSection(currentNav);


        // dimensions

        if(this.sectionDimensions)
        {
            this.$dimensionField.removeClass('hidden');

            $.each(this.sectionDimensions, $.proxy(function(key, dimension)
            {
                $('<option value="'+dimension.value+'">'+dimension.label+'</option>').appendTo(this.$dimension);

            }, this));

            var optionValue = $('option:first', this.$dimension).attr('value');

            this.$dimension.val(optionValue);
        }
        else
        {
            this.$dimensionField.addClass('hidden');
        }


        // metrics

        if(this.sectionMetrics)
        {
            $.each(this.sectionMetrics, $.proxy(function(key, metric)
            {

                $('<option value="'+metric.value+'">'+metric.label+'</option>').appendTo(this.$metric);
            }, this));
        }


        // table type

        if(this.sectionEnabledCharts)
        {
            this.hideTableTypes();

            $.each(this.sectionEnabledCharts, $.proxy(function(key, enabledChart)
            {
                this.showTableType(enabledChart);
            }, this));
        }
        else
        {
            this.showTableTypes();
            this.hideTableType('geo');
            this.hideTableType('counter');
            this.hideTableType('area');
        }


        // default chart

        if(this.sectionChart)
        {
            this.$tableTypeBtns.removeClass('active');
            $('[data-tabletype="'+this.sectionChart+'"]', this.$tableType).addClass('active');
        }
        else
        {
            this.$tableTypeBtns.removeClass('active');
            $('.btn:first', this.$tableType).addClass('active');
        }


        // realtime

        if(this.sectionRealtime)
        {
            this.$period.addClass('hidden');
            this.startRealtime();
        }
        else
        {
            this.$period.removeClass('hidden');
            this.stopRealtime();
        }
    },


    // set section

    setSection: function(nav)
    {
        var section = false;

        $.each(AnalyticsBrowserData, $.proxy(function(sectionKey, sectionObject)
        {
            if(sectionKey == nav)
            {
                section = sectionObject;
            }
        }, this));

        this.sectionUri = false;
        this.sectionView = false;
        this.sectionDimensions = false;
        this.sectionMetrics = false;
        this.sectionEnabledCharts = false;
        this.sectionRealtime = false;
        this.sectionChart = false;

        if(section)
        {
            if(typeof(section.dimensions) !== 'undefined')
            {
                this.sectionDimensions = section.dimensions;
            }

            if(typeof(section.metrics) !== 'undefined')
            {
                this.sectionMetrics = section.metrics;
            }

            if(typeof(section.enabledCharts) !== 'undefined')
            {
                this.sectionEnabledCharts = section.enabledCharts;
            }

            if(typeof(section.realtime) !== 'undefined')
            {
                this.sectionRealtime = section.realtime;
            }

            if(typeof(section.chart) !== 'undefined')
            {
                this.sectionChart = section.chart;
            }

            if(typeof(section.uri) !== 'undefined')
            {
                this.sectionUri = section.uri;
            }

            if(typeof(section.view) !== 'undefined')
            {
                this.sectionView = section.view;
            }
        }
    },

    applySettings: function(settings)
    {
        if(typeof(settings.menu) !== 'undefined')
        {
            this.currentMenu = settings.menu;
        }

        if(typeof(settings.dimension) !== 'undefined')
        {
            this.currentDimension = settings.dimension;
        }

        if(typeof(settings.metric) !== 'undefined')
        {
            this.currentMetric = settings.metric;
        }

        if(typeof(settings.chart) !== 'undefined')
        {
            this.currentChart = settings.chart;
        }

        if(typeof(settings.period) !== 'undefined')
        {
            this.currentPeriod = settings.period;
        }

        if(typeof(settings.pinned) !== 'undefined')
        {
            this.pinned = eval(settings.pinned);
        }
    },

    saveState: function()
    {
        var currentChart = $('.btn.active', this.$tableType).data('tabletype');

        var data = {
            id: this.$widget.data('widget-id'),
            menu: this.$menu.val(),
            dimension: this.$dimension.val(),
            metric: this.$metric.val(),
            chart: currentChart,
            period: this.$periodSelect.val(),
            pinned: this.pinned
        };

        Craft.queueActionRequest('analytics/explorer/saveWidgetState', data, $.proxy(function(response)
        {

            // state saved

        }, this));
    },

    onOpen: function(ev)
    {
        var link = $(ev.currentTarget);
        var accountId = link.data('account-id');
        var propertyId = link.data('property-id');
        var profileId = link.data('profile-id');
        var uri = this.sectionUri;

        var url = 'https://www.google.com/analytics/web/?pli=1#'+uri+'/a'+accountId+'w'+propertyId+'p'+profileId+'/';

        window.open(url, '_blank');
    },

    onPin: function()
    {
        if(!this.$pin.hasClass('active'))
        {
            this.pin();
        }
        else
        {
            this.unpin();
        }

        this.saveState();
    },

    pin: function()
    {
        this.$pin.addClass('active');

        this.$collapsible.animate({
            opacity: 0,
            height: "toggle"
        }, 200);

        this.pinned = 1;
    },

    unpin: function()
    {
        this.$pin.removeClass('active');

        this.$collapsible.animate({
            opacity: 1,
            height: "toggle"
        }, 200);

        this.pinned = 0;
    },


    /*************

    BrowserView

    **************/

    onChangeDimension: function(ev)
    {
        this.currentDimension = $(ev.currentTarget).val();
        this.saveState();
        this.browse();
    },

    onChangeMetric: function(ev)
    {
        this.currentMetric = $(ev.currentTarget).val();
        this.saveState();
        this.browse();
    },

    browse: function()
    {
        var data = {
            id: this.$widget.data('widget-id'),
            metric: this.$metric.val(),
            period: this.$periodSelect.val(),
            realtime: 0
        };

        if(!this.$dimensionField.hasClass('hidden'))
        {
            data.dimension = this.$dimension.val();
        }

        if(this.sectionRealtime)
        {
            data.realtime = 1;
        }

        this.requestData = data;

        this.request(data);
    },

    request: function(data)
    {
        this.$spinner.removeClass('hidden');

        $('[data-view]', this.$element).addClass('hidden');
        $('[data-view="'+this.sectionView+'"]', this.$element).removeClass('hidden');

        switch(this.sectionView)
        {
            case 'realtimeVisitors':
                $('.analytics-toolbar').addClass('hidden');
                var realtimeVisitors = new Analytics.RealtimeVisitors(this.$element);
                realtimeVisitors.request(data);
                break;

            case 'browser':
                $('.analytics-toolbar').removeClass('hidden');
                this.requestBrowser(data);
                break;
        }
    },

    requestBrowser: function(data)
    {
        var chart = $('.btn.active', this.$tableType).data('tabletype');

        Craft.queueActionRequest('analytics/explorer/'+chart, data, $.proxy(function(response, textStatus)
        {
            if(textStatus == 'success' && typeof(response.error) == 'undefined')
            {
                this.$browser.removeClass('hidden');
                this.$error.addClass('hidden');
                this.handleBrowserResponse(response, chart);
            }
            else
            {
                this.$browser.addClass('hidden');
                this.$error.html(response.error);
                this.$error.removeClass('hidden');
            }

            this.$spinner.addClass('hidden');

        }, this));
    },

    handleBrowserResponse: function(response, chart)
    {
        console.log('chart', chart);

        switch(chart)
        {
            case "area":
            this.handleAreaChartResponse(response);
            break;

            case "geo":
            this.handleGeoChartResponse(response);
            break;

            case "pie":
            this.handlePieChartResponse(response);
            break;

            case "table":
            this.handleTableChartResponse(response);
            break;

            case "counter":
            this.handleCounterResponse(response);
            break;
        }

        if(typeof(response.dimension) != 'undefined')
        {
            this.$infosDimension.removeClass('hidden');
            this.$infosDimension.html(response.dimension);
        }
        else
        {
            this.$infosDimension.addClass('hidden');
        }

        this.$infosMetric.html(response.metric);
        this.$infosPeriod.html(response.period);

        this.changeTableType(chart);


        // realtime no visitors

        var noVisitors = false;

        if(this.sectionRealtime)
        {
            if(typeof(response.table) != 'undefined' && typeof(response.table.rows) != 'undefined')
            {
                if(response.table.rows.length == 0)
                {
                    noVisitors = true;
                }
            }
            else
            {
                noVisitors = true;
            }
        }

        if(noVisitors)
        {
            this.$infosDimension.addClass('hidden');
            this.$infosMetric.addClass('hidden');
            this.$infosPeriod.addClass('hidden');


            $('.analytics-no-visitors').removeClass('hidden');
        }
        else
        {
            this.$infosDimension.removeClass('hidden');
            this.$infosMetric.removeClass('hidden');
            this.$infosPeriod.removeClass('hidden');

            $('.analytics-no-visitors').addClass('hidden');
        }

        this.resize();
    },

    fillSingleChartData: function(chartResponse)
    {
        this.singleChartData = new google.visualization.DataTable();

        $.each(chartResponse.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.singleChartData.addColumn(column.type, column.label);
        }, this));

        rows = chartResponse.rows;
        rows = AnalyticsUtils.parseRows(chartResponse.columns, chartResponse.rows);

        this.singleChartData.addRows(rows);
    },

    handleAreaChartResponse: function(response)
    {
        this.$infosCount.html(response.total);

        this.fillSingleChartData(response.area);
        this.singleChartOptions = Analytics.ChartOptions.area;

        console.log('singleChartOptions', this.singleChartOptions);
        if(this.$periodSelect.val() == 'week')
        {
            this.singleChartOptions.hAxis.format = 'E';
            this.singleChartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.$periodSelect.val() == 'month')
        {
            this.singleChartOptions.hAxis.format = 'MMM d';
            this.singleChartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.$periodSelect.val() == 'year')
        {
            this.singleChartOptions.hAxis.showTextEvery = 1;
            this.singleChartOptions.hAxis.format = 'MMM yy';

            var dateFormatter = new google.visualization.DateFormat({
                pattern: "MMMM yyyy"
            });

            dateFormatter.format(this.singleChartData, 0);
        }


        var realChart = $('<div>');
        this.singleChart = new google.visualization.AreaChart(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData, this.singleChartOptions);
    },

    handleCounterResponse: function(response)
    {
        this.$counterValue.html(response.counter.count);
        this.$counterLabel.html(response.metric);
        this.$counterPeriod.html(response.period);
    },

    handleGeoChartResponse: function(response)
    {
        this.fillSingleChartData(response.table);
        this.singleChartOptions = false;

        var realChart = $('<div>');
        this.singleChart = new google.visualization.GeoChart(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData);
    },

    handleTableChartResponse: function(response)
    {
        this.fillSingleChartData(response.table);
        this.singleChartOptions = Analytics.ChartOptions.table;

        var realChart = $('<div>');
        this.singleChart = new google.visualization.Table(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData, this.singleChartOptions);
    },

    handlePieChartResponse: function(response)
    {
        this.fillSingleChartData(response.table);
        this.singleChartOptions = Analytics.ChartOptions.pie;

        var realChart = $('<div>');
        this.singleChart = new google.visualization.PieChart(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData, this.singleChartOptions);
    },

    resize: function()
    {
        if(this.singleChart)
        {
            this.singleChart.draw(this.singleChartData, this.singleChartOptions);
        }

        var total = 0;

        $.each($('.analytics-toolbar select, .analytics-toolbar .btngroup', this.$element), function() {
            total += $(this).width() + 20;
        });

        if(total < this.$widget.width())
        {
            this.$widget.removeClass('analytics-small');
        }
        else
        {
            this.$widget.addClass('analytics-small');
        }
    },

    onPeriodChange: function(ev)
    {
        this.currentPeriod = $(ev.currentTarget).val();
        this.saveState();
        this.browse();
    },

    startRealtime: function()
    {
        if(this.timer)
        {
            this.stopRealtime();
        }

        this.timer = setInterval($.proxy(function()
        {
            var data = this.requestData;

            if(data)
            {
                this.request(data);
            }

        }, this), AnalyticsRealtimeInterval * 1000);
    },

    stopRealtime: function()
    {
        clearInterval(this.timer);
    },


    // TableTypes

    showTableTypes: function()
    {
        $('.btn', this.$disabledTableType).appendTo(this.$tableType);
    },

    hideTableTypes: function()
    {
        $('.btn', this.$tableType).appendTo(this.$disabledTableType);
    },

    showTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$element).appendTo(this.$tableType);
    },

    hideTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$element).appendTo(this.$disabledTableType);
    },

    onTableTypeChange: function(ev)
    {
        var tableType = $(ev.currentTarget).data('tabletype');

        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        this.saveState();
        this.browse();
    },

    changeTableType: function(tableType)
    {
        this.currentChart = tableType;

        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        if(tableType == 'table')
        {
            this.$counter.addClass('hidden');

            if(this.sectionDimensions)
            {
                this.$dimensionField.removeClass('hidden');
                this.$infosDimension.removeClass('hidden');
            }
        }
        else if(tableType == 'area')
        {
            this.$counter.addClass('hidden');
            this.$dimensionField.addClass('hidden');
        }
        else if(tableType == 'geo')
        {
            this.$counter.addClass('hidden');
        }
        else if(tableType == 'counter')
        {
            this.$counter.removeClass('hidden');
            this.$dimensionField.addClass('hidden');
        }
        else
        {
            this.$counter.addClass('hidden');

            if(this.sectionDimensions)
            {
                this.$dimensionField.removeClass('hidden');
                this.$infosDimension.removeClass('hidden');
            }
        }

        this.resize();
    },
});







