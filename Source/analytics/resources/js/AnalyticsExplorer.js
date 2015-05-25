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
        this.$element = $('#'+element);
        this.$widget = $('.analytics-widget:first', this.$element);
        this.$views = $('.analytics-view', this.$element);
        this.$error = $('.analytics-error', this.$element);

        this.view = false;
        this.views = {};
        this.section = false;
        this.settings = settings;
        this.loaded = false;

        this.addListener(Garnish.$win, 'resize', 'resize');

        this.visualizationLoad();
    },

    loadInterface: function()
    {
        var defaults = this.settings;

        if(!defaults.menu)
        {
            defaults.menu = 'audienceOverview';
        }

        // pin button

        this.$pinBtn = $('.analytics-pin', this.$element);
        this.$collapsible = $('.analytics-collapsible', this.$element);

        this.pinBtn = new Analytics.PinBtn(this.$pinBtn, this.$collapsible, {
            pinned: this.settings.pinned,
            onPinChange: $.proxy(this, 'onPinChange')
        });

        // open
        this.$open = $('.analytics-open', this.$element);
        this.addListener(this.$open, 'click', 'onOpen');

        // menu
        this.$menu = $('.analytics-menu:first select:first', this.$element);
        this.menu = new Analytics.Menu(this.$menu, {
            defaultMenu: defaults.menu,
            onMenuChange: $.proxy(this, 'onMenuChange')
        });

        // browserView
        this.views.browser = new Analytics.BrowserView(this);

        // realtimeVisitorsView
        this.views.realtimeVisitors = new Analytics.RealtimeVisitorsView(this);


        // trigger menu change
        this.menu.onMenuChange(false, false);

        // set default values
        switch(this.section.view)
        {
            case 'browser':

            this.views.browser.metrics.val(defaults.metric);
            this.views.browser.dimensions.val(defaults.dimension);
            this.views.browser.period.val(defaults.period);

            if(defaults.chart)
            {
                this.views.browser.tableTypes.val(defaults.chart);
            }

            this.views.browser.browse();

            break;

            case 'realtimeVisitors':

            break;
        }
    },

    onMenuChange: function(currentMenu, browse, saveState)
    {
        // hide all views
        this.$views.addClass('hidden');

        // section
        this.section = this.getSection(currentMenu);

        switch(this.section.view)
        {
            case 'browser':

            this.views.realtimeVisitors.disable();
            this.views.browser.dimensions.setOptions(this.section.dimensions);
            this.views.browser.metrics.setOptions(this.section.metrics);
            this.views.browser.tableTypes.setOptions(this.section.enabledCharts);
            this.views.browser.tableTypes.val(this.section.chart);

            if(browse)
            {
                this.views.browser.browse();
            }
            break;

            case 'realtimeVisitors':
            this.views.realtimeVisitors.enable();
            break;
        }

        // set current view
        this.view = this.views[this.section.view];

        // show view
        $('[data-view="'+this.section.view+'"]', this.$element).removeClass('hidden');


        // save state

        if(typeof(saveState) == 'undefined')
        {
            saveState = true;
        }

        if(saveState)
        {
            this.saveState();
        }


        // resize

        if(this.view.resize)
        {
            this.view.resize();
        }
    },

    onOpen: function(ev)
    {
        var link = $(ev.currentTarget);
        var accountId = link.data('account-id');
        var propertyId = link.data('property-id');
        var profileId = link.data('profile-id');
        var uri = this.section.uri;

        var url = 'https://www.google.com/analytics/web/?pli=1#'+uri+'/a'+accountId+'w'+propertyId+'p'+profileId+'/';

        window.open(url, '_blank');
    },

    onPinChange: function()
    {
        this.saveState();
    },

    visualizationLoad: function()
    {
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
                this.loadInterface();
            }
        }, this));
    },

    saveState: function()
    {
        this.view.saveState();
    },

    resize: function()
    {
        if(this.view.resize)
        {
            this.view.resize();
        }
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

        $.each(AnalyticsBrowserData, function(sectionKey, sectionObject)
        {
            if(sectionKey == menu)
            {
                $.each(sectionObject, function(foundSectionKey, foundSectionObject)
                {
                    section[foundSectionKey] = foundSectionObject;
                });

                return false;
            }
        });

        return section;
    },
});

/**
 * Browser View
 */
Analytics.BrowserView = Garnish.Base.extend({

    init: function(explorer)
    {
        this.explorer = explorer;

        this.$element = this.explorer.$element;

        this.browser = false;

        // dimensions
        this.$dimensionsField = $('.analytics-dimension-field', this.$element);
        this.dimensions = new Analytics.SelectField(this.$dimensionsField, { onChange: $.proxy(this, 'onDimensionsChange') });

        // metrics
        this.$metricsField = $('.analytics-metric-field', this.$element);
        this.metrics = new Analytics.SelectField(this.$metricsField, { onChange: $.proxy(this, 'onMetricsChange') });

        this.$tableTypes = $('.analytics-tabletypes:first', this.$element);
        this.tableTypes = new Analytics.TableTypes(this.$tableTypes, {
            onChange: $.proxy(this, 'onTableTypesChange')
        });

        this.$periodField = $('.analytics-period', this.$element);
        this.period = new Analytics.SelectField(this.$periodField, {
            onChange: $.proxy(this, 'onPeriodChange')
        });

        // this.browse();
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

        Craft.queueActionRequest('analytics/saveWidgetState', stateData, $.proxy(function(response)
        {
            // state saved

        }, this));
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
        if(this.browser)
        {
            this.browser.disable();
            this.browser = false;
        }

        data = {
            metrics: this.metrics.val(),
            dimensions: this.dimensions.val(),
            tableType: this.tableTypes.val(),
            period: this.period.val(),
            realtime: 0,
        };

        if(typeof(this.explorer.section.realtime) != 'undefined' && this.explorer.section.realtime)
        {
            data.realtime = 1;
        }

        this.browser = new Analytics.Browser(this.explorer, data);
    },

    resize: function()
    {
        var widths = {
            dimensionsWidth: $('select', this.$dimensionsField).width(),
            metricsWidth: $('select', this.$metricsField).width(),
            tableTypesWidth: $('.analytics-enabled-tabletypes', this.$tableTypes).width(),
            periodWidth: $('select', this.$periodField).width(),
        };


        var total = 0;

        $.each(widths, function(key, value) {
            total += value + 30;
        });


        var widgetWidth = this.explorer.$collapsible.width() - 3*24;

        // console.log('diff', total, widths, widgetWidth);

        if(total < widgetWidth)
        {
            this.explorer.$widget.removeClass('analytics-small');
        }
        else
        {
            this.explorer.$widget.addClass('analytics-small');
        }

        if(this.browser)
        {
            this.browser.resize();
        }
    }
});


/**
 * Browser
 */
Analytics.Browser = Garnish.Base.extend({
    init: function(explorer, data)
    {
        this.explorer = explorer;
        this.$element = this.explorer.$element;

        this.$spinner = $('.spinner', this.$element);
        this.$nodata = $('.analytics-no-data', this.$element);
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

        this.request();

        if(this.data.realtime)
        {
            this.startRealtime();
        }
    },

    disable: function()
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
        var chart = this.data.tableType;

        this.$spinner.removeClass('body-loading');
        this.$spinner.removeClass('hidden');

        Craft.queueActionRequest('analytics/explorer/'+chart, this.data, $.proxy(function(response, textStatus)
        {
            if(textStatus == 'success' && typeof(response.error) == 'undefined')
            {
                this.$browserContent.removeClass('hidden');
                this.explorer.$error.addClass('hidden');
                this.handleResponse(response, chart);
            }
            else
            {
                this.$browserContent.addClass('hidden');
                this.explorer.$error.html(response.error);
                this.explorer.$error.removeClass('hidden');
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
        var totalRows = 0;

        switch(chart)
        {
            case "area":
            totalRows = response.area.rows.length;
            this.handleChartResponse(chart, response);
            break;

            case "geo":
            case "pie":
            case "table":
            totalRows = response.table.rows.length;
            this.handleChartResponse(chart, response);
            break;

            case "counter":
            this.handleCounterResponse(response);
            break;
        }


        // dimension

        if(typeof(response.dimension) != 'undefined')
        {
            this.$infosDimension.removeClass('hidden');
            this.$infosDimension.html(response.dimension);
        }
        else
        {
            this.$infosDimension.addClass('hidden');
        }


        // metric

        if(typeof(response.metric) != 'undefined')
        {
            this.$infosMetric.removeClass('hidden');
            this.$infosMetric.html(response.metric);
        }
        else
        {
            this.$infosMetric.addClass('hidden');
        }

        // infos count

        if(typeof(response.total) != 'undefined')
        {
            this.$infosCount.html(response.total);
            this.$infosCount.removeClass('hidden');
        }
        else
        {
            this.$infosCount.addClass('hidden');
        }


        // period

        if(typeof(response.period) != 'undefined')
        {
            this.$infosPeriod.html(response.period);
            this.$infosPeriod.removeClass('hidden');
        }
        else
        {
            this.$infosPeriod.addClass('hidden');
        }


        // show/hide elements

        if(chart == 'counter')
        {
            this.$counter.removeClass('hidden');
            this.$chart.addClass('hidden');
            this.$infos.addClass('hidden');
            this.$nodata.addClass('hidden');
        }
        else
        {
            this.$counter.addClass('hidden');
            this.$chart.removeClass('hidden');
            this.$infos.removeClass('hidden');

            if(totalRows > 0)
            {
                this.$nodata.addClass('hidden');
                this.$chart.removeClass('hidden');
            }
            else
            {
                this.$nodata.removeClass('hidden');
                this.$chart.addClass('hidden');
            }
        }

        this.resize();
    },


    handleCounterResponse: function(response)
    {
        this.$counterValue.html(response.counter.count);
        this.$counterLabel.html(response.metric);
        this.$counterPeriod.html(response.period);
    },

    handleChartResponse: function(chart, response)
    {
        var realChart = $('<div>');

        switch(chart)
        {
            case 'area':
            this.chartData = AnalyticsUtils.responseToDataTable(response.area);
            this.chartOptions = AnalyticsChartOptions.area(this.data.period);

            if(this.data.period == 'year')
            {
                var dateFormatter = new google.visualization.DateFormat({
                    pattern: "MMMM yyyy"
                });

                dateFormatter.format(this.chartData, 0);
            }

            this.chart = new google.visualization.AreaChart(realChart.get(0));
            break;

            case 'geo':
            this.chartData = AnalyticsUtils.responseToDataTable(response.table);
            this.chartOptions = AnalyticsChartOptions.geo(this.data.dimensions);
            this.chart = new google.visualization.GeoChart(realChart.get(0));
            break;

            case 'table':
            this.chartData = AnalyticsUtils.responseToDataTable(response.table);
            this.chartOptions = AnalyticsChartOptions.table();
            this.chart = new google.visualization.Table(realChart.get(0));
            break;

            case 'pie':
            this.chartData = AnalyticsUtils.responseToDataTable(response.table);
            this.chartOptions = AnalyticsChartOptions.pie();
            this.chart = new google.visualization.PieChart(realChart.get(0));
            break;
        }

        this.$chart.html('');
        this.$chart.append(realChart);
        this.chart.draw(this.chartData, this.chartOptions);
    },

    resize: function()
    {
        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
        }
    },
});

/**
 * RealtimeVisitors View
 */
Analytics.RealtimeVisitorsView = Garnish.Base.extend({
    init: function(explorer)
    {
        this.explorer = explorer;
        this.$element = this.explorer.$element;
        this.realtimeVisitors = new Analytics.RealtimeVisitors(this.explorer);
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

        Craft.queueActionRequest('analytics/saveWidgetState', stateData, $.proxy(function(response)
        {
            // state saved

        }, this));
    },

    enable: function()
    {
        this.realtimeVisitors.enable();
    },

    disable: function()
    {
        this.realtimeVisitors.disable();
    }
});


/**
 * RealtimeVisitors
 */
Analytics.RealtimeVisitors = Garnish.Base.extend({

    init: function(explorer)
    {
        this.explorer = explorer;
        this.$element = this.explorer.$element;
        this.$realtimeVisitors = $('.analytics-realtime-visitors', this.$element);
        this.$spinner = $('.spinner', this.$element);

        this.timer = false;
    },

    enable: function()
    {
        this.request();
        this.startRealtime();
    },

    disable: function()
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
                this.explorer.$error.addClass('hidden');
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
                this.explorer.$error.html(msg);
                this.explorer.$error.removeClass('hidden');
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
 * Menu
 */
Analytics.Menu = Garnish.Base.extend({
    init: function(menuElement, settings)
    {
        this.$menu = menuElement;
        this.settings = settings;

        if(typeof(settings.defaultMenu) != 'undefined')
        {
            this.$menu.val(settings.defaultMenu);
        }

        this.addListener(this.$menu, 'change', 'onMenuChange');
    },

    val: function()
    {
        return this.$menu.val();
    },

    onMenuChange: function(browse, saveState)
    {
        value = this.$menu.val();

        this.settings.onMenuChange(value, browse, saveState);
    }
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

        if(typeof(settings.pinned) != 'undefined')
        {
            this.pinned = settings.pinned;
        }
        else
        {
            this.pinned = 0;
        }

        if(this.pinned)
        {
            this.$pinBtn.addClass('active');
            this.$collapsible.addClass('analytics-collapsed');

            this.$collapsible.animate({
                opacity: 0,
                height: "toggle"
            }, 0);
        }
        else
        {
            this.$collapsible.css('visibility', 'visible');
        }

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

    pin: function(saveState)
    {
        this.$pinBtn.addClass('active');
        this.$collapsible.addClass('analytics-collapsed');
        this.pinned = 1;

        this.$collapsible.animate({
            opacity: 0,
            height: "toggle"
        }, 200);

        this.settings.onPinChange(saveState);
    },

    unpin: function()
    {
        this.$pinBtn.removeClass('active');
        this.$collapsible.removeClass('analytics-collapsed');
        this.pinned = 0;
        this.$collapsible.css('visibility', 'visible');
        this.$collapsible.animate({
            opacity: 1,
            height: "toggle"
        }, 200);

        this.settings.onPinChange();
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

        this.addListener(this.$tableTypeBtns, 'click', 'onTableTypeChange');

        this.$tableTypeBtns.removeClass('active');
    },

    setOptions: function (options, defaultValue)
    {
        this.hideTableTypes();

        if(typeof(options) != 'undefined' && options)
        {
            $.each(options, $.proxy(function(key, option)
            {
                this.showTableType(option)
            }, this));
        }
        else
        {
            this.showTableType('pie');
            this.showTableType('table');
        }

        this.$tableTypeBtns.removeClass('active');

        var firstBtn = $('.btn:first', this.$enabledTableTypes);
        this.value = firstBtn.data('tabletype');
        firstBtn.addClass('active');
    },

    val: function(val)
    {
        if(typeof(val) != 'undefined' && val)
        {
            this.value = val;

            this.$tableTypeBtns.removeClass('active');
            $('[data-tabletype="'+this.value+'"]', this.$enabledTableTypes).addClass('active');

            return this.value;
        }
        else
        {
            return this.value;
        }
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
 * SelectField
 */
Analytics.SelectField = Garnish.Base.extend({
    init: function(fieldElement, settings)
    {
        this.$field = fieldElement;
        this.$select = $('select', this.$field);

        this.settings = settings;

        this.addListener(this.$select, 'change', 'onChange');
    },

    setOptions: function(options)
    {
        this.$select.html('');

        if(options)
        {
            this.$field.removeClass('hidden');

            $.each(options, $.proxy(function(key, option)
            {
                $('<option value="'+option.value+'">'+option.label+'</option>').appendTo(this.$select);
            }, this));
        }
        else
        {
            this.$field.addClass('hidden');
        }

        if($('option[value="'+this.val()+'"]', this.$select).length > 0)
        {
            this.val(this.val());
        }
        else
        {
            this.val($('option:first', this.$select).val());
        }
    },

    val: function(val)
    {
        if(typeof(val) != 'undefined' && val)
        {
            return this.$select.val(val);
        }
        else
        {
            return this.$select.val();
        }
    },

    onChange: function(ev)
    {
        this.value = $(ev.currentTarget).val();

        this.settings.onChange(ev);
    }
});

})(jQuery);
