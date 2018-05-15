/** global: Analytics */
/** global: google */
/**
 * Pie
 */
Analytics.reports.Pie = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.dimension);
            $period.html(this.data.metric + " " + this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);
            this.chartOptions = Analytics.ChartOptions.pie();
            this.chart = new google.visualization.PieChart(this.$graph.get(0));

            this.chartOptions.height = this.$graph.height();

            this.addChartReadyListener();
        }
    });
