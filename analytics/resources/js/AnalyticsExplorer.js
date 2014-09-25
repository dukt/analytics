(function($) {

google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsExplorer = Garnish.Base.extend({
    init: function(element, settings)
    {
        this.timer = false;
        this.requestData = false;

        this.currentMenu = 'audienceOverview';
        this.currentChart = 'table';
        this.currentPeriod = 'month';
        this.pinned = 0;

        this.chartArea = false;
        this.chartTable = false;
        this.chartPie = false;

        this.tableData = false;
        this.chartAreaData = false;

        this.$element = $('#'+element);
        this.$menu = $('.analytics-menu:first select:first', this.$element);
        this.$widget = $('.analytics-widget:first', this.$element);
        this.$chart = $('.analytics-chart', this.$element);
        this.$pie = $('.analytics-piechart', this.$element);
        this.$table = $('.analytics-table', this.$element);
        this.$dimensionField = $('.analytics-dimension-field', this.$element);
        this.$dimension = $('.analytics-dimension select', this.$element);
        this.$metric = $('.analytics-metric select', this.$element);
        this.$total = $('.analytics-total', this.$element);
        this.$totalCount = $('.analytics-count', this.$total);
        this.$totalLabel = $('.analytics-label', this.$total);
        this.$tableType = $('.analytics-tabletype:first', this.$element);
        this.$disabledTableType = $('.analytics-disabled-tabletype:first', this.$element);
        this.$tableTypeBtns = $('.analytics-tabletype .btn', this.$element);
        this.$period = $('.analytics-period', this.$element);
        this.$periodSelect = $('.analytics-period select', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$pin = $('.analytics-pin', this.$element);
        this.$infosDimension = $('.analytics-infos-dimension', this.$element);
        this.$infosMetric = $('.analytics-infos-metric', this.$element);
        this.$infosCount = $('.analytics-infos-count', this.$element);
        this.$counter = $('.analytics-counter', this.$element);
        this.$counterValue = $('.analytics-counter-value', this.$element);
        this.$counterLabel = $('.analytics-counter-label', this.$element);
        this.$collapsible = $('.analytics-collapsible', this.$element);

        this.addListener(this.$menu, 'change', 'onMenuChange');
        this.addListener(this.$tableTypeBtns, 'click', 'onTableTypeChange');
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

        // default menu
        this.$menu.val(this.currentMenu);
        this.changeMenu(this.currentMenu);

        // dimension
        this.$dimension.val(this.currentDimension);

        // metric
        this.$metric.val(this.currentMetric);

        // chart
        this.changeTableType(this.currentChart);

        // period
        this.$period.val(this.currentPeriod);

    },

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
            this.pinned = settings.pinned;
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
        }
    },


    // browse

    browse: function()
    {
        var data = {
            id: this.$widget.data('widget-id'),
            metric: this.currentMetric,
            period: this.currentPeriod,
            realtime: 0
        };

        if(this.currentDimension)
        {
            data.dimension = this.currentDimension;
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

        Craft.postActionRequest('analytics/browse/combined', data, $.proxy(function(response)
        {
            if(typeof(response.error) == 'undefined')
            {
                this.updateAreaChart(response.chart);
                this.updatePieAndTableChart(response.table);
                this.updateTotal(response.total);
            }

            this.$infosDimension.html(this.$dimension.val());
            this.$infosMetric.html(this.$metric.val());

            var chart = $('.btn.active', this.$tableType).data('tabletype');
            this.changeTableType(chart);

            this.$spinner.addClass('hidden');
        }, this));
    },


    // update charts

    updateAreaChart: function(response)
    {
        this.chartAreaData = new google.visualization.DataTable();

        $.each(response.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.chartAreaData.addColumn(column.type, column.label);
        }, this));

        this.chartAreaData.addRows(response.rows);

        if(!this.chartArea)
        {
            this.chartArea = new google.visualization.AreaChart(this.$chart.get(0));
        }

        this.chartArea.draw(this.chartAreaData, this.areaChartOptions);
    },

    updateTotal: function(response)
    {
        this.$counterValue.html(response.count);
        this.$counterLabel.html(response.label);
        this.$infosCount.html(response.count);
        this.$totalCount.html(response.count);
        this.$totalLabel.html(response.label);
    },

    updatePieAndTableChart: function(response)
    {
        this.tableData = new google.visualization.DataTable();

        $.each(response.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.tableData.addColumn(column.type, column.label);
        }, this));

        this.tableData.addRows(response.rows);

        if(!this.chartTable)
        {
            this.chartTable = new google.visualization.Table(this.$table.get(0));
        }

        this.chartTable.draw(this.tableData, this.tableChartOptions);

        if(!this.chartPie)
        {
            this.chartPie = new google.visualization.PieChart(this.$pie.get(0));
        }

        this.chartPie.draw(this.tableData, this.pieChartOptions);
    },


    // resize

    resize: function()
    {
        if(this.chartArea)
        {
            this.chartArea.draw(this.chartAreaData, this.areaChartOptions);
        }

        if(this.chartTable)
        {
            this.chartTable.draw(this.tableData, this.tableChartOptions);
        }

        if(this.chartPie)
        {
            this.chartPie.draw(this.tableData, this.pieChartOptions);
        }
    },


    // period

    onPeriodChange: function(ev)
    {
        this.currentPeriod = $(ev.currentTarget).val();
        this.saveState();
        this.browse();
    },


    // saveState

    saveState: function()
    {
        var data = {
            id: this.$widget.data('widget-id'),
            menu: this.currentMenu,
            dimension: this.currentDimension,
            metric: this.currentMetric,
            chart: this.currentChart,
            period: this.currentPeriod,
            pinned: this.pinned
        };

        console.log('saveState', data);

        Craft.postActionRequest('analytics/browse/saveState', data, $.proxy(function(response)
        {
            console.log('response', response);
        }, this));
    },


    // menu

    onMenuChange: function(ev)
    {
        $value = $(ev.currentTarget).val();
        this.currentMenu = $value;
        this.currentNav = $value;
        this.changeMenu($value);

        // save state
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
                $('<option value="'+dimension+'">'+dimension+'</option>').appendTo(this.$dimension);

            }, this));

            if(!this.currentDimension)
            {
                var optionValue = $('option:first', this.$dimension).attr('value');
                this.currentDimension = optionValue;
            }
            else
            {
                if($('option[value="'+this.currentDimension+'"]').length == 0)
                {
                    var optionValue = $('option:first', this.$dimension).attr('value');
                    this.currentDimension = optionValue;
                }
            }
        }
        else
        {
            this.$dimensionField.addClass('hidden');
            this.currentDimension = false;
        }

        // metrics
        if(this.sectionMetrics)
        {
            $.each(this.sectionMetrics, $.proxy(function(key, metric)
            {

                $('<option value="'+metric+'">'+metric+'</option>').appendTo(this.$metric);
            }, this));

            if(!this.currentMetric)
            {
                this.currentMetric = this.$metric.val();
            }
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
            this.hideTableType('counter');
            this.hideTableType('area');
        }


        // default chart

        if(this.sectionChart)
        {
            this.$tableTypeBtns.removeClass('active');
            $('[data-tabletype="'+this.sectionChart+'"]', this.$tableType).addClass('active');
            this.currentChart = this.sectionChart;
        }
        else
        {
            this.$tableTypeBtns.removeClass('active');
            $('.btn:first', this.$tableType).addClass('active');

            this.currentChart = $('.btn:first', this.$tableType).data('tabletype');
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

        this.browse();
    },


    // real-time

    startRealtime: function()
    {
        this.timer = setInterval($.proxy(function()
        {

            var data = this.requestData;

            if(data)
            {
                this.request(data);
            }

        }, this), 10000);
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
        this.changeTableType(tableType);
        this.saveState();
    },

    changeTableType: function(tableType)
    {
        console.log('changeTableType', tableType);
        this.currentChart = tableType;

        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        if(tableType == 'table')
        {
            this.$infosCount.addClass('hidden');
            this.$table.removeClass('hidden');
            this.$counter.addClass('hidden');
            this.$pie.addClass('hidden');
            this.$chart.addClass('hidden');

            if(this.sectionDimensions)
            {
                this.$dimensionField.removeClass('hidden');
                this.$infosDimension.removeClass('hidden');
            }
        }
        else if(tableType == 'area')
        {
            this.$infosDimension.removeClass('hidden');
            this.$infosCount.removeClass('hidden');
            this.$chart.removeClass('hidden');
            this.$table.addClass('hidden');
            this.$pie.addClass('hidden');
            this.$counter.addClass('hidden');
            this.$dimensionField.addClass('hidden');
        }
        else if(tableType == 'counter')
        {
            this.$infosDimension.addClass('hidden');
            this.$infosCount.addClass('hidden');
            this.$chart.addClass('hidden');
            this.$table.addClass('hidden');
            this.$pie.addClass('hidden');
            this.$counter.removeClass('hidden');
            this.$dimensionField.addClass('hidden');
            this.$infosDimension.addClass('hidden');
        }
        else
        {
            this.$infosCount.addClass('hidden');
            this.$pie.removeClass('hidden');
            this.$chart.addClass('hidden');
            this.$table.addClass('hidden');
            this.$counter.addClass('hidden');

            if(this.sectionDimensions)
            {
                this.$dimensionField.removeClass('hidden');
                this.$infosDimension.removeClass('hidden');
            }
        }

        this.resize();
    },


    // pin

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


    // chart options

    areaChartOptions: {
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
            textPosition: 'in',
            textStyle: {
                color: '#058DC7'
            },
            showTextEvery: 5,
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

    pieChartOptions: {
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
        sliceVisibilityThreshold: 0
    },

    tableChartOptions: {
        page: 'enable'
    },
});


})(jQuery);
