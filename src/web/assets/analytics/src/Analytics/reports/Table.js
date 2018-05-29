/** global: Analytics */
/** global: google */
/**
 * Table
 */
Analytics.reports.Table = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.metric);
            $period.html(this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);
            this.chartOptions = Analytics.ChartOptions.table();
            this.chart = new google.visualization.Table(this.$graph.get(0));

            this.addChartReadyListener();
        },

        resize: function() {
            // disable resize for the table chart
        },
    });