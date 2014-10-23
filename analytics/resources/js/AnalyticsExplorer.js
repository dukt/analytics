(function($) {

if (typeof Analytics == 'undefined')
{
    Analytics = {};
}

var googleVisualisationCalled = false;


/**
 * Explorer
 */
Analytics.Explorer = Garnish.Base.extend({
    init: function(element, settings)
    {
        console.log('Explorer');

        this.$element = $('#'+element);
        this.$widget = $('.analytics-widget:first', this.$element);
        this.$views = $('.analytics-view', this.$element);
        this.view = false;
        this.section = false;


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
                // let's get started (browse ?)

                this.initActions();
                this.initMenu();
            }
        }, this));
    },

    initMenu: function()
    {
        console.log('initMenu');

        this.$menu = $('.analytics-menu:first select:first', this.$element);

        this.menu = new Analytics.Menu(this.$menu, {
            onMenuChange: $.proxy(function(currentMenu, saveState) {

                // change view

                if(this.view)
                {
                    this.view.destroy();
                }

                this.$views.addClass('hidden');

                section = this.getSection(currentMenu);

                // explorerStateData = {
                //     id: this.$widget.data('widget-id'),
                //     menu: currentMenu,
                //     pinned: this.pinBtn.val(),
                // };

                switch(section.view)
                {
                    case 'realtimeVisitors':
                    this.view = new Analytics.RealtimeVisitorsView(this, this.$element);
                    break;

                    case 'browser':
                    this.view = new Analytics.BrowserView(this, this.$element, section);
                    break;
                }

                $('[data-view="'+section.view+'"]', this.$element).removeClass('hidden');

                this.section = section;

                if(saveState)
                {
                    this.saveState();
                }
            }, this)
        });

    },

    saveState: function()
    {
        this.view.saveState();
    },

    initActions: function()
    {
        // pin button

        $pinBtn = $('.analytics-pin', this.$element);
        $collapsible = $('.analytics-collapsible', this.$element);

        this.pinBtn = new Analytics.PinBtn($pinBtn, $collapsible, {
            onPinChange: $.proxy(function() {
                this.saveState();
            }, this)
        });
    },

    getSection: function(menu)
    {
        var section = {
            uri: false,
            view: false,
            dimensions: false,
            metrics: false,
            enabledCharts: false,
            realtime: false,
            chart: false,
        };

        var foundSection = null;

        $.each(AnalyticsBrowserData, $.proxy(function(sectionKey, sectionObject)
        {
            if(sectionKey == menu)
            {
                foundSection = sectionObject;
            }
        }, this));

        if(foundSection)
        {
            if(typeof(foundSection.dimensions) !== 'undefined')
            {
                section.dimensions = foundSection.dimensions;
            }

            if(typeof(foundSection.metrics) !== 'undefined')
            {
                section.metrics = foundSection.metrics;
            }

            if(typeof(foundSection.enabledCharts) !== 'undefined')
            {
                section.enabledCharts = foundSection.enabledCharts;
            }

            if(typeof(foundSection.realtime) !== 'undefined')
            {
                section.realtime = foundSection.realtime;
            }

            if(typeof(foundSection.chart) !== 'undefined')
            {
                section.chart = foundSection.chart;
            }

            if(typeof(foundSection.uri) !== 'undefined')
            {
                section.uri = foundSection.uri;
            }

            if(typeof(foundSection.view) !== 'undefined')
            {
                section.view = foundSection.view;
            }
        }

        return section;
    },
});


/**
 * Menu
 */
Analytics.Menu = Garnish.Base.extend({
    init: function(menuElement, settings)
    {
        this.$menu = menuElement;
        this.settings = settings;
        this.section = false;
        this.defaultMenu = 'audienceOverview';
        this.currentMenu = false;

        this.addListener(this.$menu, 'change', 'onMenuChange');

        this.changeMenu(this.defaultMenu, false);
    },

    val: function()
    {
        return this.currentMenu;
    },

    onMenuChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.currentMenu = value;

        this.changeMenu(value);
    },

    changeMenu: function(currentMenu, saveState)
    {
        if(typeof(saveState) == 'undefined')
        {
            saveState = true;
        }

        this.currentMenu = currentMenu;

        this.$menu.val(currentMenu);

        this.settings.onMenuChange(this.currentMenu, saveState);
    },
});


/**
 * Pin Button
 */
Analytics.PinBtn = Garnish.Base.extend({
    init: function(pinBtnElement, collapsibleElement, settings)
    {
        this.$pinBtn = pinBtnElement;
        this.$collapsible = collapsibleElement;
        this.settings = settings;
        this.pinned = 0;
        this.addListener(this.$pinBtn, 'click', 'onPin');
    },

    val: function()
    {
        return this.pinned;
    },

    onPin: function()
    {
        if(!this.$pinBtn.hasClass('active'))
        {
            this.pin();
        }
        else
        {
            this.unpin();
        }
    },

    pin: function()
    {
        this.$pinBtn.addClass('active');
        this.$collapsible.addClass('analytics-collapsed');
        this.pinned = 1;

        // this.$collapsible.animate({
        //     opacity: 0,
        //     height: "toggle"
        // }, 200);

        this.$collapsible.transition({height: '0px'}, 200, 'ease-out');

        this.settings.onPinChange();
    },

    unpin: function()
    {
        this.$pinBtn.removeClass('active');
        this.$collapsible.removeClass('analytics-collapsed');
        this.pinned = 0;

        // this.$collapsible.animate({
        //     opacity: 1,
        //     height: "toggle"
        // }, 200);

        this.$collapsible.transition({height: 'auto'}, 200, 'ease-out');

        this.settings.onPinChange();
    }
});


/**
 * Browser View
 */
Analytics.BrowserView = Garnish.Base.extend({
    init: function(explorer, element, section)
    {
        console.log('BrowserView');
        this.$element = element;

        this.$menu = $('.analytics-menu:first select:first', this.$element);
        this.$metricsField = $('.analytics-metric-field', this.$element);
        this.$dimensionsField = $('.analytics-dimension-field', this.$element);
        this.$tableTypes = $('.analytics-tabletypes:first', this.$element);
        this.$periodField = $('.analytics-period', this.$element);

        this.explorer = explorer;
        this.browser = false;
        this.section = section;

        this.metrics = new Analytics.MetricsField(this.$metricsField, section.metrics, {
            onChange: $.proxy(this, 'onMetricsChange')
        });

        this.dimensions = new Analytics.DimensionsField(this.$dimensionsField, section.dimensions, {
            onChange: $.proxy(this, 'onDimensionsChange')
        });

        this.tableTypes = new Analytics.TableTypes(this.$tableTypes, {
            enabledCharts: section.enabledCharts,
            defaultChart: section.chart,
            onChange: $.proxy(this, 'onTableTypesChange')
        });

        this.period = new Analytics.PeriodField(this.$periodField, {
            onChange: $.proxy(this, 'onPeriodChange')
        });

        this.browse();
    },

    saveState: function(data)
    {
        var stateData = {
            id: this.explorer.$widget.data('widget-id'),

            settings: {
                menu: this.explorer.menu.val(),
                pinned: this.explorer.pinBtn.val(),
                metric: this.metrics.val(),
                dimension: this.dimensions.val(),
                chart: this.tableTypes.val(),
                period: this.period.val(),
            }
        };

        console.log('save state', stateData);

        Craft.queueActionRequest('analytics/explorer/saveWidgetState', stateData, $.proxy(function(response)
        {
            // state saved

        }, this));
    },

    destroy: function()
    {
        this.metrics.destroy();
        this.dimensions.destroy();
        this.tableTypes.destroy();
        this.period.destroy();

        if(this.browser)
        {
            this.browser.destroy();
            this.browser = false;
        }
    },

    onMetricsChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
        this.saveState();
    },

    onDimensionsChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
        this.saveState();
    },

    onTableTypesChange: function(value)
    {
        this.browse();
        this.saveState();
    },

    onPeriodChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
        this.saveState();
    },

    browse: function()
    {
        console.log('browse');

        if(this.browser)
        {
            this.browser.destroy();
            this.browser = false;
        }

        data = {
            metrics: this.metrics.val(),
            dimensions: this.dimensions.val(),
            tableType: this.tableTypes.val(),
            period: this.period.val(),
            realtime: 0,
        };

        if(typeof(this.section.realtime) != 'undefined' && this.section.realtime)
        {
            data.realtime = 1;
        }

        this.browser = new Analytics.Browser(this.$element, data);
    },
});


/**
 * Browser
 */
Analytics.Browser = Garnish.Base.extend({
    init: function(element, data)
    {
        console.log('init AnalyticsBrowser');

        this.$element = element;

        this.$spinner = $('.spinner', this.$element);
        this.$error = $('.analytics-error', this.$element);
        this.$browser = $('.analytics-browser:first', this.$element);
        this.$browserContent = $('.analytics-browser-content:first', this.$element);
        this.$widget = $('.analytics-widget:first', this.$element);

        this.$infos = $('.analytics-infos', this.$element);
        this.$infosDimension = $('.analytics-infos-dimension', this.$element);
        this.$infosMetric = $('.analytics-infos-metric', this.$element);
        this.$infosPeriod = $('.analytics-infos-period', this.$element);
        this.$infosCount = $('.analytics-infos-count', this.$element);

        this.$chart = $('.analytics-single-chart', this.$element);

        this.$counter = $('.analytics-counter', this.$element);
        this.$counterValue = $('.analytics-counter-value', this.$element);
        this.$counterLabel = $('.analytics-counter-label', this.$element);
        this.$counterPeriod = $('.analytics-counter-period', this.$element);

        this.timer = false;
        this.data = data;

        this.addListener(Garnish.$win, 'resize', 'resize');

        this.request();

        // this.resize();

        if(this.data.realtime)
        {
            this.startRealtime();
        }
    },

    destroy: function()
    {
        this.removeListener(Garnish.$win, 'resize');
        this.stopRealtime();
    },

    startRealtime: function()
    {
        if(this.timer)
        {
            this.stopRealtime();
        }

        this.timer = setInterval($.proxy(function()
        {
            this.request();

        }, this), AnalyticsRealtimeInterval * 1000);
    },

    stopRealtime: function()
    {
        clearInterval(this.timer);
    },

    request: function()
    {
        console.log('request', this.data);

        var chart = this.data.tableType;

        this.$spinner.removeClass('body-loading');
        this.$spinner.removeClass('hidden');

        Craft.queueActionRequest('analytics/explorer/'+chart, this.data, $.proxy(function(response, textStatus)
        {
            if(textStatus == 'success' && typeof(response.error) == 'undefined')
            {
                this.$browserContent.removeClass('hidden');
                this.$error.addClass('hidden');
                this.handleResponse(response, chart);
            }
            else
            {
                this.$browserContent.addClass('hidden');
                this.$error.html(response.error);
                this.$error.removeClass('hidden');
            }

            this.$chart.removeClass('analytics-chart-area');
            this.$chart.removeClass('analytics-chart-table');
            this.$chart.removeClass('analytics-chart-pie');
            this.$chart.removeClass('analytics-chart-counter');
            this.$chart.removeClass('analytics-chart-geo');

            this.$chart.addClass('analytics-chart-'+chart);

            this.$spinner.addClass('hidden');

        }, this));
    },


    handleResponse: function(response, chart)
    {
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

        if(typeof(response.metric) != 'undefined')
        {
            this.$infosMetric.removeClass('hidden');
            this.$infosMetric.html(response.metric);
        }
        else
        {
            this.$infosMetric.addClass('hidden');
        }

        if(typeof(response.total) != 'undefined')
        {
            this.$infosCount.html(response.total);
            this.$infosCount.removeClass('hidden');
        }
        else
        {
            this.$infosCount.addClass('hidden');
        }

        this.$infosPeriod.html(response.period);

        if(chart == 'counter')
        {
            this.$counter.removeClass('hidden');
            this.$chart.addClass('hidden');
            this.$infos.addClass('hidden');
        }
        else
        {
            this.$counter.addClass('hidden');
            this.$chart.removeClass('hidden');
            this.$infos.removeClass('hidden');
        }

        this.resize();
    },

    fillChartData: function(chartResponse)
    {
        this.chartData = new google.visualization.DataTable();

        $.each(chartResponse.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.chartData.addColumn(column.type, column.label);
        }, this));

        rows = chartResponse.rows;
        rows = AnalyticsUtils.parseRows(chartResponse.columns, chartResponse.rows);

        this.chartData.addRows(rows);
    },

    handleAreaChartResponse: function(response)
    {
        this.fillChartData(response.area);
        this.chartOptions = Analytics.ChartOptions.area;

        console.log('chartOptions', this.chartOptions);

        if(this.data.period == 'week')
        {
            this.chartOptions.hAxis.format = 'E';
            this.chartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.data.period == 'month')
        {
            this.chartOptions.hAxis.format = 'MMM d';
            this.chartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.data.period == 'year')
        {
            this.chartOptions.hAxis.showTextEvery = 1;
            this.chartOptions.hAxis.format = 'MMM yy';

            var dateFormatter = new google.visualization.DateFormat({
                pattern: "MMMM yyyy"
            });

            dateFormatter.format(this.chartData, 0);
        }


        var realChart = $('<div>');
        this.chart = new google.visualization.AreaChart(realChart.get(0));
        this.$chart.html('');
        this.$chart.append(realChart);
        this.chart.draw(this.chartData, this.chartOptions);
    },

    handleCounterResponse: function(response)
    {
        this.$counterValue.html(response.counter.count);
        this.$counterLabel.html(response.metric);
        this.$counterPeriod.html(response.period);
    },

    handleGeoChartResponse: function(response)
    {
        this.fillChartData(response.table);
        this.chartOptions = Analytics.ChartOptions.geo;

        var realChart = $('<div>');
        this.chart = new google.visualization.GeoChart(realChart.get(0));
        this.$chart.html('');
        this.$chart.append(realChart);
        this.chart.draw(this.chartData, this.chartOptions);
    },

    handleTableChartResponse: function(response)
    {
        this.fillChartData(response.table);
        this.chartOptions = Analytics.ChartOptions.table;

        var realChart = $('<div>');
        this.chart = new google.visualization.Table(realChart.get(0));
        this.$chart.html('');
        this.$chart.append(realChart);
        this.chart.draw(this.chartData, this.chartOptions);
    },

    handlePieChartResponse: function(response)
    {
        this.fillChartData(response.table);
        this.chartOptions = Analytics.ChartOptions.pie;

        var realChart = $('<div>');
        this.chart = new google.visualization.PieChart(realChart.get(0));
        this.$chart.html('');
        this.$chart.append(realChart);
        this.chart.draw(this.chartData, this.chartOptions);
    },

    resize: function()
    {
        console.log('Analytics.Browser.resize()');

        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
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
});

/**
 * PeriodField
 */
Analytics.PeriodField = Garnish.Base.extend({
    init: function(fieldElement, settings)
    {
        this.$field = fieldElement;
        this.$select = $('select', this.$field);

        this.settings = settings;

        this.addListener(this.$select, 'change', 'onChange');
    },

    val: function()
    {
        return this.$select.val();
    },

    destroy: function()
    {
        this.removeListener(this.$select, 'change');
    },

    onChange: function(ev)
    {
        this.settings.onChange(ev);
    }
});


/**
 * DimensionsField
 */
Analytics.DimensionsField = Garnish.Base.extend({
    init: function(fieldElement, dimensions, settings)
    {
        this.$field = fieldElement;
        this.$dimension = $('select', this.$field);

        this.settings = settings;

        this.addListener(this.$dimension, 'change', 'onChange');

        this.$dimension.html('');

        if(dimensions)
        {
            this.$field.removeClass('hidden');

            $.each(dimensions, $.proxy(function(key, dimension)
            {
                $('<option value="'+dimension.value+'">'+dimension.label+'</option>').appendTo(this.$dimension);

            }, this));

            var optionValue = $('option:first', this.$dimension).attr('value');

            this.$dimension.val(optionValue);
        }
        else
        {
            this.$field.addClass('hidden');
        }
    },

    val: function()
    {
        return this.$dimension.val();
    },

    destroy: function()
    {
        this.removeListener(this.$dimension, 'change');
    },

    onChange: function(ev)
    {
        this.settings.onChange(ev);
    }
});


/**
 * MetricsField
 */
Analytics.MetricsField = Garnish.Base.extend({
    init: function(fieldElement, metrics, settings)
    {
        this.$field = fieldElement;
        this.$metric = $('select', this.$field);

        this.settings = settings;

        this.addListener(this.$metric, 'change', 'onChange');

        this.$metric.html('');

        if(metrics)
        {
            this.$field.removeClass('hidden');

            $.each(metrics, $.proxy(function(key, metric)
            {
                $('<option value="'+metric.value+'">'+metric.label+'</option>').appendTo(this.$metric);
            }, this));
        }
        else
        {
            this.$field.addClass('hidden');
        }
    },

    val: function()
    {
        return this.$metric.val();
    },

    destroy: function()
    {
        this.removeListener(this.$metric, 'change');
    },

    onChange: function(ev)
    {
        this.settings.onChange(ev);
    }
});


/**
 * TableTypes
 */
Analytics.TableTypes = Garnish.Base.extend({
    init: function(tableTypesElement, settings)
    {
        this.$tableTypes = tableTypesElement;
        this.$enabledTableTypes = $('.analytics-enabled-tabletypes:first', this.$tableTypes);
        this.$disabledTableTypes = $('.analytics-disabled-tabletypes:first', this.$tableTypes);
        this.$tableTypeBtns = $('.btn', this.$tableTypes);

        this.settings = settings;
        this.value = false;

        this.addListener(this.$tableTypeBtns, 'click', 'onTableTypeChange');

        this.$tableTypeBtns.removeClass('active');

        if(settings.enabledCharts)
        {
            this.hideTableTypes();

            $.each(settings.enabledCharts, $.proxy(function(key, enabledChart)
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

        // select chart

        if(settings.defaultChart)
        {
            $('[data-tabletype="'+settings.defaultChart+'"]', this.$enabledTableTypes).addClass('active');
        }
        else
        {
            $('.btn:first', this.$enabledTableTypes).addClass('active');
        }

        this.value = $('.btn.active:first', this.$tableTypes).data('tabletype');
    },

    val: function()
    {
        return this.value;
    },

    destroy: function()
    {
        this.removeListener(this.$tableTypeBtns, 'click');
    },

    showTableTypes: function()
    {
        $('.btn', this.$disabledTableTypes).appendTo(this.$enabledTableTypes);
    },

    hideTableTypes: function()
    {
        $('.btn', this.$enabledTableTypes).appendTo(this.$disabledTableTypes);
    },

    showTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$tableTypes).appendTo(this.$enabledTableTypes);
    },

    hideTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$tableTypes).appendTo(this.$disabledTableTypes);
    },

    onTableTypeChange: function(ev)
    {
        var tableType = $(ev.currentTarget).data('tabletype');

        this.$tableTypeBtns.removeClass('active');

        $('[data-tabletype="'+tableType+'"]', this.$enabledTableTypes).addClass('active');

        this.value = tableType;

        this.settings.onChange(this.value);
    },
});


/**
 * RealtimeVisitors View
 */
Analytics.RealtimeVisitorsView = Garnish.Base.extend({
    init: function(explorer, element)
    {
        this.$element = element;

        this.explorer = explorer;

        console.log('hello real time visitors view');

        this.realtimeVisitors = new Analytics.RealtimeVisitors(this.$element);
    },

    saveState: function()
    {
        var stateData = {
            id: this.explorer.$widget.data('widget-id'),

            settings: {
                menu: this.explorer.menu.val(),
                pinned: this.explorer.pinBtn.val(),
            }
        };

        console.log('save state', stateData);

        Craft.queueActionRequest('analytics/explorer/saveWidgetState', stateData, $.proxy(function(response)
        {
            // state saved

        }, this));
    },

    destroy: function()
    {
        this.realtimeVisitors.destroy();
    }
});


/**
 * RealtimeVisitors
 */
Analytics.RealtimeVisitors = Garnish.Base.extend({

    init: function(element)
    {
        this.$element = element;
        this.$realtimeVisitors = $('.analytics-realtime-visitors', this.$element);
        this.$error = $('.analytics-error', this.$element);
        this.$spinner = $('.spinner', this.$element);

        this.timer = false;

        this.request();
        this.startRealtime();
    },

    destroy: function()
    {
        this.stopRealtime();
    },

    startRealtime: function()
    {
        if(this.timer)
        {
            this.stopRealtime();
        }

        this.timer = setInterval($.proxy(function()
        {
            this.request();

        }, this), AnalyticsRealtimeInterval * 1000);
    },

    stopRealtime: function()
    {
        clearInterval(this.timer);
    },

    request: function()
    {
        this.$spinner.removeClass('body-loading');
        this.$spinner.removeClass('hidden');

        Craft.queueActionRequest('analytics/explorer/realtimeVisitors', {}, $.proxy(function(response, textStatus)
        {
            if(textStatus == 'success' && typeof(response.error) == 'undefined')
            {
                this.$realtimeVisitors.removeClass('hidden');
                this.$error.addClass('hidden');
                this.handleResponse(response);
            }
            else
            {
                msg = 'An unknown error occured.';

                if(typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined')
                {
                    msg = response.error;
                }

                this.$realtimeVisitors.addClass('hidden');
                this.$error.html(msg);
                this.$error.removeClass('hidden');
            }

            this.$spinner.addClass('hidden');

        }, this));
    },

    handleResponse: function(response)
    {
        var newVisitor = response.newVisitor;
        var returningVisitor = response.returningVisitor;

        var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

        $('.active-visitors .count', this.$realtimeVisitors).text(calcTotal);

        if (calcTotal > 0) {
            $('.progress', this.$realtimeVisitors).removeClass('hidden');
            $('.legend', this.$realtimeVisitors).removeClass('hidden');
        }
        else
        {
            $('.progress', this.$realtimeVisitors).addClass('hidden');
            $('.legend', this.$realtimeVisitors).addClass('hidden');
        }

        if(calcTotal > 0)
        {
            var blue = Math.round(100 * newVisitor / calcTotal);
        }
        else
        {
            var blue = 100;
        }

        var green = 100 - blue;

        // blue

        $('.progress-bar.blue', this.$realtimeVisitors).css('width', blue+'%');
        $('.progress-bar.blue span', this.$realtimeVisitors).text(blue+'%');

        if(blue > 0)
        {
            $('.progress-bar.blue', this.$realtimeVisitors).removeClass('hidden');
        }
        else
        {
            $('.progress-bar.blue', this.$realtimeVisitors).addClass('hidden');
        }

        // green

        $('.progress-bar.green', this.$realtimeVisitors).css('width', green+'%');
        $('.progress-bar.green span', this.$realtimeVisitors).text(green+'%');

        if(green > 0)
        {
            $('.progress-bar.green', this.$realtimeVisitors).removeClass('hidden');
        }
        else
        {
            $('.progress-bar.green', this.$realtimeVisitors).addClass('hidden');
        }
    },
});


/**
 * ChartOptions
 */
Analytics.ChartOptions = Garnish.Base.extend({}, {
    area: {
        height: 150,
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
        height: 282,
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
        page: 'enable'
    }
});


})(jQuery);
