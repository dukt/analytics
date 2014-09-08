google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsBrowser = Garnish.Base.extend({
    init: function(widget)
    {
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
        this.$chart = $('.analytics-chart', this.$browser).get(0);
        this.$pie = $('.analytics-piechart', this.$browser).get(0);
        this.$table = $('.analytics-table', this.$browser).get(0);
        this.$dimension = $('.analytics-dimension select', this.$browser);
        this.$metric = $('.analytics-metric select', this.$browser);
        this.$total = $('.analytics-total', this.$browser);
        this.$tableType = $('.analytics-tabletype:first', this.$browser);
        this.$tableTypeBtns = $('.analytics-tabletype .btn', this.$browser);
        this.$period = $('.analytics-period:first', this.$browser);
        this.$periodBtns = $('.analytics-period .btn', this.$browser);
        this.$spinner = $('.spinner', this.$browser);

        this.$spinner.removeClass('body-loading');

        // table type
        this.addListener(this.$tableTypeBtns, 'click', { }, function(ev) {
            this.onTableTypeChange($(ev.currentTarget).data('tabletype'));
        });

        // period
        this.addListener(this.$periodBtns, 'click', { }, function(ev) {
            this.onPeriodChange($(ev.currentTarget).data('period'));
            this.browse();
        });

        // menu
        this.changeCurrentNav('audience');

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

    changeCurrentNav: function(currentNav)
    {

        this.$currentNav = currentNav;


        // update dimensions select

        this.$dimension.html('');

        $.each(AnalyticsBrowserSections, $.proxy(function(sectionKey, section) {
            if(sectionKey == this.$currentNav)
            {
                $.each(section.dimensions, $.proxy(function(dimension, dimensionTitle) {

                    $('<option value="'+dimension+'">'+dimensionTitle+'</option>').appendTo(this.$dimension);

                }, this));
            }
        }, this));


        // update metrics select

        this.$metric.html('');

        $.each(AnalyticsBrowserSections, $.proxy(function(sectionKey, section) {

            if(sectionKey == this.$currentNav)
            {
                $.each(section.metrics, $.proxy(function(metric, metricTitle) {
                    $('<option value="'+metric+'">'+metricTitle+'</option>').appendTo(this.$metric);
                }, this));
            }
        }, this));


        // browse

        this.browse();
    },

    onTableTypeChange: function(tableType)
    {
        this.currentTableType = tableType;
        this.$tableTypeBtns.removeClass('active');
        $('[data-tabletype="'+tableType+'"]', this.$tableType).addClass('active');

        if(tableType == 'table')
        {
            $('.analytics-table', this.$browser).removeClass('hidden');
            $('.analytics-piechart', this.$browser).addClass('hidden');
        }
        else
        {
            $('.analytics-table', this.$browser).addClass('hidden');
            $('.analytics-piechart', this.$browser).removeClass('hidden');
        }
    },

    onPeriodChange: function(period) {
        this.currentPeriod = period;
        this.$periodBtns.removeClass('active');
        $('[data-period="'+period+'"]', this.$period).addClass('active');
    },

    browse: function()
    {
        this.$spinner.removeClass('hidden');

        var data = {
            id: this.$widget.data('widget-id'),
            dimension: this.$dimension.val(),
            metric: this.$metric.val(),
            period: this.currentPeriod
        };

        Craft.postActionRequest('analytics/browse/combined', data, $.proxy(function(response) {
            if(typeof(response.error) == 'undefined')
            {
                this.updateChart(response.chart);
                this.updateTable(response.table);
                this.updateTotal(response.total);
            }
            else
            {
                console.log('error');
            }

            this.$spinner.addClass('hidden');
        }, this));

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

