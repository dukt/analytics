google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});


AnalyticsCountReport = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $(element);
        this.$inject = $('.analytics-inject', element);
        this.$error = $('.analytics-error', element);

        var data = {
            start: this.$element.data('start'),
            end: this.$element.data('end')
        };

        this.$element.addClass('analytics-loading');

        Craft.queueActionRequest('analytics/charts/getCountReport', data, $.proxy(function(response) {

            if(typeof(response.error) != 'undefined') {
                $('.inject', this.$error).html(response.error);
                this.$error.removeClass('hidden');

                // $(v).parents('.analyticsTab').find('.more').addClass('hidden');


                $(this.$element).addClass('error');
            } else {
                this.$inject.html(response.html);
            }

            this.$element.removeClass('analytics-loading');
        }, this));
    },
});

AnalyticsChart = Garnish.Base.extend({
    $element: null,
    $inject: null,
    $data: null,
    $chart: null,
    $googleData: null,
    $googleChart: null,
    $lastWindowWidth: null,

    init: function(element)
    {
        this.$element = element;
        this.$inject = $('.inject', element);
        this.$error = $('.analytics-error', element);

        this.$data = $('.data', element);
        this.$data.css('display', 'none');

        this.$data = this.$data.html();
        this.$data = $.parseJSON(this.$data);

        Craft.queueActionRequest('analytics/charts/getChart', {data:this.$data}, $.proxy(function(response) {

            if(typeof(response.error) != 'undefined')
            {
                $('.inject', this.$error).html(response.error);

                this.$error.removeClass('hidden');

                $(this.$element).addClass('error');
            }
            else
            {
                this.initChart(response.chart);
            }

            $(this.$element).removeClass('analytics-loading');

        }, this));
    },

    initChart: function(chart)
    {
        this.$chart = chart;
        this.$googleData = new google.visualization.DataTable();

        // columns
        var $this = this;
        $.each(chart.columns, function(k, col) {
            $this.$googleData.addColumn(col.type, col.label);
        });

        // rows
        this.$googleData.addRows(chart.rows);

        // draw the chart

        switch(chart.type) {

            case 'AreaChart':
            this.$googleChart = new google.visualization.AreaChart(this.$inject.get(0));
            break;

            case 'LineChart':
            this.$googleChart = new google.visualization.LineChart(this.$inject.get(0));
            break;

            case 'Table':
            this.$googleChart = new google.visualization.Table(this.$inject.get(0));
            break;

            case 'ColumnChart':
            this.$googleChart = new google.visualization.ColumnChart(this.$inject.get(0));
            break;

            case 'BubbleChart':
            this.$googleChart = new google.visualization.BubbleChart(this.$inject.get(0));
            break;

            case 'BarChart':
            this.$googleChart = new google.visualization.BarChart(this.$inject.get(0));
            break;

            case 'ColumnChart':
            this.$googleChart = new google.visualization.ColumnChart(this.$inject.get(0));
            break;

            case 'PieChart':
            this.$googleChart = new google.visualization.PieChart(this.$inject.get(0));
            break;

            case 'GeoChart':
            this.$googleChart = new google.visualization.GeoChart(this.$inject.get(0));
            break;

        }

        this.drawChart();

        $('body').bind('redraw', function(e){
            $this.drawChart();
        });

        $(window).resize(function() {
            var el = $($this.$element);

            if(el.width() > 0 && $this.$lastWindowWidth != el.width())
            {
                $this.drawChart();
            }

            $this.$lastWindowWidth = el.width();
        });
    },

    drawChart: function()
    {
        if(this.$googleChart)
        {
            this.$googleChart.draw(this.$googleData, this.$chart.options);
        }
    }
});

$(document).ready(function() {
    var charts = $(".analytics-chart");

    var rawCharts = [];

    charts.each(function(k, el) {
        rawCharts = new AnalyticsChart(el);
    });


    var sparks = $(".analytics-report-count");

    var rawSparks = [];

    sparks.each(function(k, el) {
        rawSparks = new AnalyticsCountReport(el);
    });
});