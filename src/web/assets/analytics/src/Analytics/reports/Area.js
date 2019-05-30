/** global: Analytics */
/** global: google */
/**
 * Area
 */
Analytics.reports.Area = Analytics.reports.BaseChart.extend(
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

            this.chartOptions = Analytics.ChartOptions.area(this.data.period);

            if (typeof(this.data.chartOptions) != 'undefined') {
                $.extend(this.chartOptions, this.data.chartOptions);
            }


            // generate ticks

            var ticks = [];

            for (i = 0; i < this.data.chart.rows.length; i++) {
                var rowDate = this.data.chart.rows[i][0]
                var tickYear = rowDate.substr(0, 4)
                var tickMonth = rowDate.substr(4, 2) - 1
                var tickDay = rowDate.substr(6, 2)
                var tickDate = new Date(tickYear, tickMonth, tickDay)
                ticks.push(tickDate)
            }

            this.chartOptions.hAxis.ticks = ticks


            // period

            if (this.data.period == 'year') {
                var dateFormatter = new google.visualization.DateFormat({
                    pattern: "MMMM yyyy"
                });

                dateFormatter.format(this.dataTable, 0);
            }

            this.chart = new google.visualization.AreaChart(this.$graph.get(0));

            this.addChartReadyListener();
        }
    });
