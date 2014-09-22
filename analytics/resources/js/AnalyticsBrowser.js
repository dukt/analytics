google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsBrowser = Garnish.Base.extend({
    init: function(widget)
    {
        this.realtimeTimer = false;
        this.requestData = false;
        this.currentTableType = 'table';
        this.currentPeriod = 'month';

        this.chart = false;
        this.chartData = false;
        this.table = false;
        this.tableData = false;
        this.pie = false;
        this.table = false;

        this.$element = $('#'+widget);
        this.$widget = $('.analytics-widget:first', this.$element);
        this.$browser = $('.analytics-browser:first', this.$widget);
        this.$browserHeader = $('.analytics-browser-header:first', this.$element);
        this.$chart = $('.analytics-chart', this.$browser).get(0);
        this.$pie = $('.analytics-piechart', this.$browser).get(0);
        this.$table = $('.analytics-table', this.$browser).get(0);
        this.$dimension = $('.analytics-dimension select', this.$browserHeader);
        this.$metric = $('.analytics-metric select', this.$browserHeader);
        this.$total = $('.analytics-total', this.$browser);
        this.$tableType = $('.analytics-tabletype:first', this.$browserHeader);
        this.$tableTypeBtns = $('.analytics-tabletype .btn', this.$browserHeader);
        this.$period = $('.analytics-period', this.$element);
        this.$periodSelect = $('.analytics-period select', this.$element);
        this.$spinner = $('.spinner', this.$browser);
        this.$pinBtn = $('.analytics-pin', this.$element);

        this.$spinner.removeClass('body-loading');

        // table type
        this.addListener(this.$tableTypeBtns, 'click', { }, function(ev) {
            this.onTableTypeChange($(ev.currentTarget).data('tabletype'));
        });

        // period
        this.$periodSelect.change($.proxy(function(ev) {
            this.onPeriodChange($(ev.currentTarget).val());
            this.browse();
        }, this));

        // pin
        this.addListener(this.$pinBtn, 'click', { }, function(ev) {

            if(!this.$pinBtn.hasClass('active'))
            {
                this.pin();
            }
            else
            {
                this.unpin();
            }
        });

        // menu
        this.changeCurrentNav('browserOs');

        // dimension select
        this.$dimension.change($.proxy(function() {
            this.browse();
        }, this));

        // metric select
        this.$metric.change($.proxy(function() {
            this.browse();
        }, this));

        $(window).resize($.proxy(function() {
            this.resize();
        }, this));
    },

    startRealtime: function()
    {
        console.log('start realtime');

        this.realtimeTimer = setInterval($.proxy(function() {

            var data = this.requestData;

            if(data)
            {
                this.runRequest(data);
            }

        }, this), 10000);
    },

    stopRealtime: function()
    {
        console.log('stop realtime');
        clearInterval(this.realtimeTimer);
    },

    pin: function()
    {
        this.$pinBtn.addClass('active');
        $('.analytics-collapsible', this.$element).animate({
            opacity: 0,
            height: "toggle"
        }, 200, function() {
            // Animation complete.
        });
        // $('.analytics-header', this.$element).fadeOut(150);
        // $('.analytics-menu', this.$element).fadeOut(150);
    },

    unpin: function()
    {
        this.$pinBtn.removeClass('active');
        $('.analytics-collapsible', this.$element).animate({
            opacity: 1,
            height: "toggle"
        }, 200, function() {
            // Animation complete.
        });
        // $('.analytics-header', this.$element).fadeIn(150);
        // $('.analytics-menu', this.$element).fadeIn(150);
    },

    resize: function()
    {
        // redraw charts

        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
        }

        if(this.table)
        {
            this.table.draw(this.tableData, this.tableOptions);
        }

        if(this.pie)
        {
            this.pie.draw(this.tableData, this.pieOptions);
        }
    },

    getSection: function(nav)
    {
        var result = false;

        $.each(AnalyticsBrowserData, $.proxy(function(sectionKey, section) {

            if(sectionKey == nav)
            {
                result = section;
            }
        }, this));

        return result;
    },

    changeCurrentNav: function(currentNav)
    {
        this.$currentNav = currentNav;


        // update selects

        this.$dimension.html('');
        this.$metric.html('');

        var section = this.getSection(currentNav);

        // dimensions

        if(section && typeof(section.dimensions) !== 'undefined' && section.dimensions.length > 0)
        {
            $('.analytics-dimension-field', this.$element).removeClass('hidden');

            $.each(section.dimensions, $.proxy(function(key, dimension) {

                $('<option value="'+dimension+'">'+dimension+'</option>').appendTo(this.$dimension);

            }, this));
        }
        else
        {
            $('.analytics-dimension-field', this.$element).addClass('hidden');
        }

        // metrics
        if(section && typeof(section.metrics) !== 'undefined')
        {
            $.each(section.metrics, $.proxy(function(key, metric) {

                $('<option value="'+metric+'">'+metric+'</option>').appendTo(this.$metric);
            }, this));
        }

        // table type
        if(section && typeof(section.enabledCharts) !== 'undefined')
        {
            this.hideTableTypes();

            $.each(section.enabledCharts, $.proxy(function(key, enabledChart) {

                this.showTableType(enabledChart);
            }, this));
        }
        else
        {
            this.showTableTypes();
            this.hideTableType('counter');
            this.hideTableType('area');
        }


        // period

        if(section && typeof(section.realtime) !== 'undefined')
        {
            if(section.realtime)
            {
                this.$period.addClass('hidden');
            }
            else
            {
                this.$period.addClass('hidden');
            }
        }
        else
        {
            this.$period.removeClass('hidden');
        }

        // default chart

        if(section && typeof(section.chart) !== 'undefined')
        {
            this.$tableTypeBtns.removeClass('active');
            $('[data-tabletype="'+section.chart+'"]', this.$tableType).addClass('active');
        }
        else
        {
            this.$tableTypeBtns.removeClass('active');
            $('.analytics-tabletype .btn:first', this.$browserHeader).addClass('active');

        }


        // realtime

        if(section && typeof(section.realtime) !== 'undefined')
        {
            if(section.realtime)
            {
                this.startRealtime();
            }
            else
            {
                this.stopRealtime();
            }
        }
        else
        {
            this.stopRealtime();
        }



        // browse

        this.browse();
    },

    showTableTypes: function()
    {
        $('.analytics-disabled-tabletype .btn', this.$browserHeader).appendTo($('.analytics-tabletype', this.$browserHeader));
    },
    hideTableTypes: function()
    {
        $('.analytics-tabletype .btn', this.$browserHeader).appendTo($('.analytics-disabled-tabletype', this.$browserHeader));
    },

    showTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$browserHeader).appendTo($('.analytics-tabletype', this.$browserHeader));
    },

    hideTableType: function(chart)
    {
        $('[data-tabletype="'+chart+'"]', this.$browserHeader).appendTo($('.analytics-disabled-tabletype', this.$browserHeader));
    },

    onTableTypeChange: function(tableType)
    {
        var section = this.getSection(this.$currentNav);

        this.currentTableType = tableType;
        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        if(tableType == 'table')
        {
            $('.analytics-infos-count', this.$browser).addClass('hidden');
            $('.analytics-table', this.$browser).removeClass('hidden');
            $('.analytics-counter', this.$browser).addClass('hidden');
            $('.analytics-piechart', this.$browser).addClass('hidden');
            $('.analytics-chart', this.$browser).addClass('hidden');


            if(section && typeof(section.dimensions) !== 'undefined' && section.dimensions.length > 0)
            {
                $('.analytics-dimension-field', this.$browserHeader).removeClass('hidden');
                $('.analytics-infos-dimension', this.$browser).removeClass('hidden');
            }
        }
        else if(tableType == 'area')
        {

            $('.analytics-infos-dimension', this.$browser).removeClass('hidden');
            $('.analytics-infos-count', this.$browser).removeClass('hidden');
            $('.analytics-chart', this.$browser).removeClass('hidden');
            $('.analytics-table', this.$browser).addClass('hidden');
            $('.analytics-piechart', this.$browser).addClass('hidden');
            $('.analytics-counter', this.$browser).addClass('hidden');

            $('.analytics-dimension-field', this.$browserHeader).addClass('hidden');
            $('.analytics-infos-dimension', this.$browser).addClass('hidden');
        }
        else if(tableType == 'counter')
        {

            $('.analytics-infos-dimension', this.$browser).addClass('hidden');
            $('.analytics-infos-count', this.$browser).addClass('hidden');
            $('.analytics-chart', this.$browser).addClass('hidden');
            $('.analytics-table', this.$browser).addClass('hidden');
            $('.analytics-piechart', this.$browser).addClass('hidden');
            $('.analytics-counter', this.$browser).removeClass('hidden');

            $('.analytics-dimension-field', this.$browserHeader).addClass('hidden');
            $('.analytics-infos-dimension', this.$browser).addClass('hidden');
        }
        else
        {
            $('.analytics-infos-count', this.$browser).addClass('hidden');
            $('.analytics-piechart', this.$browser).removeClass('hidden');
            $('.analytics-chart', this.$browser).addClass('hidden');
            $('.analytics-table', this.$browser).addClass('hidden');
            $('.analytics-counter', this.$browser).addClass('hidden');


            if(section && typeof(section.dimensions) !== 'undefined' && section.dimensions.length > 0)
            {
                $('.analytics-dimension-field', this.$browserHeader).removeClass('hidden');
                $('.analytics-infos-dimension', this.$browser).removeClass('hidden');
            }
        }

        this.resize();
    },

    onPeriodChange: function(period) {
        this.currentPeriod = period;
    },

    browse: function()
    {
        var data = {
            id: this.$widget.data('widget-id'),
            dimension: this.$dimension.val(),
            metric: this.$metric.val(),
            period: this.currentPeriod,
            realtime: 0
        };


        var section = this.getSection(this.$currentNav);

        if(section && typeof(section.realtime) !== 'undefined' && section.realtime)
        {
            data.realtime = 1;
        }


        this.requestData = data;

        this.runRequest(data);

    },

    runRequest: function(data)
    {
        this.$spinner.removeClass('hidden');

        console.log('run request');

        Craft.postActionRequest('analytics/browse/combined', data, $.proxy(function(response) {
            if(typeof(response.error) == 'undefined')
            {
                console.log(response);
                this.updateChart(response.chart);
                this.updateTable(response.table);
                this.updateTotal(response.total);
            }
            else
            {
                console.log('error');
            }

            $('.analytics-infos-dimension', this.$browser).html(this.$dimension.val());
            $('.analytics-infos-metric', this.$browser).html(this.$metric.val());

            this.onAfterBrowse();

            this.$spinner.addClass('hidden');
        }, this));
    },

    onAfterBrowse: function()
    {
        var chart = $('.analytics-tabletype .btn.active', this.$browserHeader).data('tabletype');
        this.onTableTypeChange(chart);

        // $.each(AnalyticsBrowserData, $.proxy(function(sectionKey, section) {
        //     if(sectionKey == this.$currentNav)
        //     {
        //         // default chart

        //         if(typeof(section.chart) !== 'undefined')
        //         {
        //             this.onTableTypeChange(section.chart);
        //         }

        //     }
        // }, this));
    },

    updateChart: function(response)
    {
        this.chartData = new google.visualization.DataTable();

        $.each(response.columns, $.proxy(function(k, apiColumn) {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.chartData.addColumn(column.type, column.label);
        }, this));

        this.chartData.addRows(response.rows);

        if(!this.chart)
        {
            this.chart = new google.visualization.AreaChart(this.$chart);
        }

        this.chart.draw(this.chartData, this.chartOptions);
    },

    updateTotal: function(response)
    {
        $('.analytics-counter-value', this.$element).html(response.count);
        $('.analytics-counter-label', this.$element).html(response.label);
        $('.analytics-infos-count', this.$element).html(response.count);
        $('.analytics-count', this.$total).html(response.count);
        $('.analytics-label', this.$total).html(response.label);
    },

    updateTable: function(response)
    {
        // table chart

        this.tableData = new google.visualization.DataTable();

        $.each(response.columns, $.proxy(function(k, apiColumn) {
            var column = AnalyticsUtils.parseColumn(apiColumn);
            this.tableData.addColumn(column.type, column.label);
        }, this));

        this.tableData.addRows(response.rows);

        if(!this.table)
        {
            this.table = new google.visualization.Table(this.$table);
        }

        this.table.draw(this.tableData, this.tableOptions);

        if(!this.pie)
        {
            this.pie = new google.visualization.PieChart(this.$pie);
        }

        this.pie.draw(this.tableData, this.pieOptions);
    },





















    chartOptions: {
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

    pieOptions: {
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

    tableOptions: {
        page: 'enable'
    },
});

