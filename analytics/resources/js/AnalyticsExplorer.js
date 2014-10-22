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

                this.initMenu();
                this.initActions();
            }
        }, this));
    },

    initMenu: function()
    {
        // menu

        $menu = $('.analytics-menu:first select:first', this.$element);
        this.menu = new Analytics.Menu($menu, {
            onMenuChange: $.proxy(function(currentMenu) {

                // change view

                if(this.view)
                {
                    this.view.destroy();
                }

                this.$views.addClass('hidden');

                section = this.getSection(currentMenu);

                switch(section.view)
                {
                    case 'realtimeVisitors':
                    this.view = new Analytics.RealtimeVisitorsView();
                    break;

                    case 'browser':
                    this.view = new Analytics.BrowserView(this.$element, section);
                    break;
                }

                $('[data-view="'+section.view+'"]', this.$element).removeClass('hidden');

                this.section = section;

            }, this)
        });
    },

    initActions: function()
    {
        // pin button

        $pinBtn = $('.analytics-pin', this.$element);
        $collapsible = $('.analytics-collapsible', this.$element);

        this.pinBtn = new Analytics.PinBtn($pinBtn, $collapsible, {
            onPinChange: $.proxy(function() {
                // this.saveState();
                // console.log('saveState');
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

        this.changeMenu(this.defaultMenu);
    },

    onMenuChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.currentMenu = value;

        this.changeMenu(value);
    },

    changeMenu: function(currentMenu)
    {
        this.currentMenu = currentMenu;

        this.$menu.val(currentMenu);

        this.settings.onMenuChange(currentMenu);
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
        this.pinned = 1;

        this.$collapsible.animate({
            opacity: 0,
            height: "toggle"
        }, 200);

        this.settings.onPinChange();
    },

    unpin: function()
    {
        this.$pinBtn.removeClass('active');
        this.pinned = 0;

        this.$collapsible.animate({
            opacity: 1,
            height: "toggle"
        }, 200);

        this.settings.onPinChange();
    }
});


/**
 * Browser View
 */
Analytics.BrowserView = Garnish.Base.extend({
    init: function(element, section)
    {
        this.$element = element;

        this.$metricsField = $('.analytics-metric-field', this.$element);
        this.$dimensionsField = $('.analytics-dimension-field', this.$element);
        this.$tableTypes = $('.analytics-tabletypes:first', this.$element);
        this.$periodField = $('.analytics-period', this.$element);

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

    destroy: function()
    {
        this.metrics.destroy();
        this.dimensions.destroy();
        this.tableTypes.destroy();
        this.period.destroy();
    },

    onMetricsChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
    },

    onDimensionsChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
    },

    onTableTypesChange: function(value)
    {
        this.browse();
    },

    onPeriodChange: function(ev)
    {
        value = $(ev.currentTarget).val();

        this.browse();
    },

    browse: function()
    {
        data = {
            metrics: this.metrics.val(),
            dimensions: this.dimensions.val(),
            tableType: this.tableTypes.val(),
            period: this.period.val(),
        };

        console.log('browse', data);

        var browser = new Analytics.Browser(this.$element, data);
    },
});


/**
 * Browser
 */
Analytics.Browser = Garnish.Base.extend({
    init: function(element, data)
    {
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

        this.$singleChart = $('.analytics-single-chart', this.$element);

        this.$counter = $('.analytics-counter', this.$element);
        this.$counterValue = $('.analytics-counter-value', this.$element);
        this.$counterLabel = $('.analytics-counter-label', this.$element);
        this.$counterPeriod = $('.analytics-counter-period', this.$element);

        this.data = data;

        this.request();
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
                this.$error.addClass('hidden');
                this.handleResponse(response, chart);
            }
            else
            {
                this.$browserContent.addClass('hidden');
                this.$error.html(response.error);
                this.$error.removeClass('hidden');
            }

            this.$singleChart.removeClass('analytics-chart-area');
            this.$singleChart.removeClass('analytics-chart-table');
            this.$singleChart.removeClass('analytics-chart-pie');
            this.$singleChart.removeClass('analytics-chart-counter');
            this.$singleChart.removeClass('analytics-chart-geo');

            this.$singleChart.addClass('analytics-chart-'+chart);

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

        this.$infosMetric.html(response.metric);
        this.$infosPeriod.html(response.period);

        if(chart == 'counter')
        {
            this.$counter.removeClass('hidden');
            this.$singleChart.addClass('hidden');
            this.$infos.addClass('hidden');
        }
        else
        {
            this.$counter.addClass('hidden');
            this.$singleChart.removeClass('hidden');
            this.$infos.removeClass('hidden');
        }

        this.resize();
    },

    fillChartData: function(chartResponse)
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

        this.fillChartData(response.area);
        this.singleChartOptions = Analytics.ChartOptions.area;

        console.log('singleChartOptions', this.singleChartOptions);

        if(this.data.period == 'week')
        {
            this.singleChartOptions.hAxis.format = 'E';
            this.singleChartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.data.period == 'month')
        {
            this.singleChartOptions.hAxis.format = 'MMM d';
            this.singleChartOptions.hAxis.showTextEvery = 1;
        }
        else if(this.data.period == 'year')
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
        this.fillChartData(response.table);
        this.singleChartOptions = false;

        var realChart = $('<div>');
        this.singleChart = new google.visualization.GeoChart(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData);
    },

    handleTableChartResponse: function(response)
    {
        this.fillChartData(response.table);
        this.singleChartOptions = Analytics.ChartOptions.table;

        var realChart = $('<div>');
        this.singleChart = new google.visualization.Table(realChart.get(0));
        this.$singleChart.html('');
        this.$singleChart.append(realChart);
        this.singleChart.draw(this.singleChartData, this.singleChartOptions);
    },

    handlePieChartResponse: function(response)
    {
        this.fillChartData(response.table);
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
    init: function()
    {
        console.log('hello real time visitors view');
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
    },

    request: function(data)
    {
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

    pie: {
        theme: 'maximized',
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
