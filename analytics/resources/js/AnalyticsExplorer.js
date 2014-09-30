(function($) {

google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsExplorer = Garnish.Base.extend({
    init: function(element, settings)
    {
        console.log('settings', settings);
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
        this.$infosPeriod = $('.analytics-infos-period', this.$element);
        this.$infosCount = $('.analytics-infos-count', this.$element);
        this.$counter = $('.analytics-counter', this.$element);
        this.$counterValue = $('.analytics-counter-value', this.$element);
        this.$counterLabel = $('.analytics-counter-label', this.$element);
        this.$counterPeriod = $('.analytics-counter-period', this.$element);
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

        // chart
        this.changeTableType(this.currentChart);

        // dimension
        this.$dimension.val(this.currentDimension);

        // metric
        this.$metric.val(this.currentMetric);

        // period
        this.$period.val(this.currentPeriod);

        // browse
        this.browse();
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

    /*
    - area
    - counter
    - pie
    - table
    */
    request: function(data)
    {
        console.log('request', data);
        this.$spinner.removeClass('hidden');
        var chart = $('.btn.active', this.$tableType).data('tabletype');

        Craft.postActionRequest('analytics/browse/'+chart, data, $.proxy(function(response)
        {
            if(typeof(response.error) == 'undefined')
            {
                switch(chart)
                {
                    case "area":
                    this.updateAreaChart(response);
                    break;

                    case "pie":
                    this.updatePieAndTableChart(response);
                    break;

                    case "table":
                    this.updatePieAndTableChart(response);
                    break;

                    case "counter":
                    this.updateCounter(response);
                    break;
                }
            }

            if(typeof(response.dimension) != 'undefined')
            {
                console.log('yes');
                this.$infosDimension.removeClass('hidden');
                this.$infosDimension.html(response.dimension);
            }
            else
            {
                console.log('no');
                this.$infosDimension.addClass('hidden');
            }

            this.$infosMetric.html(response.metric);
            this.$infosPeriod.html(response.period);

            this.changeTableType(chart);

            this.$spinner.addClass('hidden');
        }, this));
    },


    // update charts

    updateAreaChart: function(response)
    {
        this.chartAreaData = new google.visualization.DataTable();

        $.each(response.area.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.chartAreaData.addColumn(column.type, column.label);
        }, this));

        this.chartAreaData.addRows(response.area.rows);

        if(!this.chartArea)
        {
            this.chartArea = new google.visualization.AreaChart(this.$chart.get(0));
        }

        this.chartArea.draw(this.chartAreaData, this.areaChartOptions);

        this.$infosCount.html(response.total);
        /// this.$infosMetric.html(response.metric);
        // this.$totalLabel.html(response.counter.label);
    },

    updateCounter: function(response)
    {
        this.$counterValue.html(response.counter.count);
        this.$counterLabel.html(response.metric);
        this.$counterPeriod.html(response.period);
        this.$infosCount.html(response.counter.count);
        this.$totalCount.html(response.counter.count);
        this.$totalLabel.html(response.metric);
    },

    updatePieAndTableChart: function(response)
    {
        this.tableData = new google.visualization.DataTable();

        $.each(response.table.columns, $.proxy(function(k, apiColumn)
        {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.tableData.addColumn(column.type, column.label);
        }, this));

        this.tableData.addRows(response.table.rows);

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

        this.browse();

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
                $('<option value="'+dimension.value+'">'+dimension.label+'</option>').appendTo(this.$dimension);

            }, this));

            var optionValue = $('option:first', this.$dimension).attr('value');

            this.$dimension.val(optionValue);

            console.log('val', this.$dimension.val());
            // if(!this.currentDimension)
            // {
            //     var optionValue = $('option:first', this.$dimension).attr('value');
            //     this.currentDimension = optionValue;
            // }
            // else
            // {
            //     if($('option[value="'+this.currentDimension+'"]').length == 0)
            //     {
            //         var optionValue = $('option:first', this.$dimension).attr('value');
            //         this.currentDimension = optionValue;
            //     }
            // }
        }
        else
        {
            this.$dimensionField.addClass('hidden');
            // this.currentDimension = false;
        }

        // metrics
        if(this.sectionMetrics)
        {
            $.each(this.sectionMetrics, $.proxy(function(key, metric)
            {

                $('<option value="'+metric.value+'">'+metric.label+'</option>').appendTo(this.$metric);
            }, this));

            // if(!this.currentMetric)
            // {
            //     this.currentMetric = this.$metric.val();
            // }
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

            // this.currentChart = this.sectionChart;
        }
        else
        {
            this.$tableTypeBtns.removeClass('active');
            $('.btn:first', this.$tableType).addClass('active');

            // this.currentChart = $('.btn:first', this.$tableType).data('tabletype');
        }


        // realtime
        console.log('this.sectionRealtime', this.sectionRealtime);
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

        // this.browse();
    },


    // real-time

    startRealtime: function()
    {
        console.log('start realtime');

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

        }, this), 4000);
    },

    stopRealtime: function()
    {
        console.log('stop realtime', this.timer);
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
        // this.changeTableType(tableType);

        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        this.saveState();
        this.browse();
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
            // this.$infosDimension.removeClass('hidden');
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
        sliceVisibilityThreshold: 1/120
    },

    tableChartOptions: {
        page: 'enable'
    },
});


})(jQuery);
