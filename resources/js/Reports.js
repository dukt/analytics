
/**
 * BaseReport
 */
Analytics.reports.BaseReport = Garnish.Base.extend(
{
    type: null,
    chart: null,
    data: null,
    period: null,
    options: null,
    visualization: null,

    $report: null,
    $chart: null,

    init: function($element, response)
    {
        this.$report = $element;
        this.$report.html('');
        this.$chart = $('<div class="chart" />').appendTo(this.$report);

        this.data = response;

        if(typeof(this.data.chartOptions) != 'undefined')
        {
            this.chartOptions = this.data.chartOptions;
        }

        if(typeof(this.data.type) != 'undefined')
        {
            this.type = this.data.type;
        }

        if(typeof(this.data.period) != 'undefined')
        {
            this.period = this.data.period;
        }

        this.addListener(Garnish.$win, 'resize', 'resize');

        this.initChart();

        if(typeof(this.data.onAfterInit) != 'undefined')
        {
            this.data.onAfterInit();
        }
    },

    initChart: function()
    {
        this.$chart.addClass(this.type);
    },

    draw: function()
    {
        if(this.dataTable && this.chartOptions)
        {
            this.chart.draw(this.dataTable, this.chartOptions);
        }
    },

    resize: function()
    {
        if(this.chart && this.dataTable && this.chartOptions)
        {
            this.chart.draw(this.dataTable, this.chartOptions);
        }
    },
});


/**
 * Area
 */
Analytics.reports.Area = Analytics.reports.BaseReport.extend(
{
    initChart: function()
    {
        this.base();

        $period = $('<div class="period" />').prependTo(this.$report);
        $title = $('<div class="title" />').prependTo(this.$report);
        $title.html(this.data.metric);
        $period.html(this.data.periodLabel);

        this.chart = new Craft.charts.Area(this.$chart);

        var chartDataTable = new Craft.charts.DataTable(this.data.dataTable);

        var scales = {
            week: 'day',
            month: 'day',
            year: 'month'
        };

        var scale = scales[this.data.period];

        var chartSettings = {
            dataScale: scale
        };

        this.chart.draw(chartDataTable, chartSettings);
    }
});

/**
 * Counter
 */
Analytics.reports.Counter = Analytics.reports.BaseReport.extend(
{
    initChart: function()
    {
        this.base();

        $value = $('<div class="value" />').appendTo(this.$chart),
        $label = $('<div class="label" />').appendTo(this.$chart),
        $period = $('<div class="period" />').appendTo(this.$chart);

        $value.html(this.data.counter.count);
        $label.html(this.data.metric);
        $period.html(' '+this.data.periodLabel);
    }
});

/**
 * Pie
 */
Analytics.reports.Pie = Analytics.reports.BaseReport.extend(
{
    initChart: function()
    {
        this.base();

        $period = $('<div class="period" />').prependTo(this.$report);
        $title = $('<div class="title" />').prependTo(this.$report);
        $title.html(this.data.dimension);
        $period.html(this.data.metric+" "+this.data.periodLabel);

        this.chart = new Craft.charts.Pie(this.$chart);

        var chartDataTable = new Craft.charts.DataTable(this.data.dataTable);

        this.chart.draw(chartDataTable);
    }
});

/**
 * Table
 */
Analytics.reports.Table = Analytics.reports.BaseReport.extend(
{
    initChart: function()
    {
        this.base();

        this.$chart.empty();

        $period = $('<div class="period" />').prependTo(this.$report);
        $title = $('<div class="title" />').prependTo(this.$report);
        $title.html(this.data.metric);
        $period.html(this.data.periodLabel);

        var $table = $('<table class="data fullwidth">').appendTo(this.$chart);
        var $thead = $('<thead>').appendTo($table);
        var $theadTr = $('<tr>').appendTo($thead);
        var $tbody = $('<tbody>').appendTo($table);

        $.each(this.data.chart.columns, function(k, v) {
            var $th = $('<th>'+v.label+'</th>').appendTo($theadTr);
        });
        $.each(this.data.chart.rows, function(rowKey, row)
        {
            var $tr = $('<tr>').appendTo($tbody);

            $.each(row, function(cellKey, cellValue)
            {
                var $td = $('<td>'+cellValue+'</td>').appendTo($tr);
            });
        });
    }
});

/**
 * Geo
 */
Analytics.reports.Geo = Analytics.reports.BaseReport.extend(
{
    initChart: function()
    {
        this.base();

        $period = $('<div class="period" />').prependTo(this.$report);
        $title = $('<div class="title" />').prependTo(this.$report);
        $title.html(this.data.metric);
        $period.html(this.data.periodLabel);

        this.chart = new Craft.charts.Map(this.$chart);

        var chartDataTable = this.data.chart;

        this.chart.draw(chartDataTable);
    }
});