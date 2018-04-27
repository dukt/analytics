/** global: Analytics */
/** global: google */
/**
 * BaseChart
 */
Analytics.reports.BaseChart = Garnish.Base.extend(
    {
        $chart: null,
        $graph: null,

        localeDefinition: null,

        type: null,
        chart: null,
        chartOptions: null,
        data: null,
        period: null,
        options: null,
        visualization: null,
        drawing: false,

        init: function($element, data, localeDefinition, chartLanguage) {
            this.visualization = new Analytics.Visualization(
                {
                    chartLanguage: chartLanguage,
                    onAfterInit: $.proxy(function() {
                        this.$chart = $element;
                        this.$chart.html('');
                        this.$graph = $('<div class="chart" />').appendTo(this.$chart);
                        this.data = data;
                        this.localeDefinition = localeDefinition;

                        if (typeof(this.data.chartOptions) != 'undefined') {
                            this.chartOptions = this.data.chartOptions;
                        }

                        if (typeof(this.data.type) != 'undefined') {
                            this.type = this.data.type;
                        }

                        if (typeof(this.data.period) != 'undefined') {
                            this.period = this.data.period;
                        }

                        this.addListener(Garnish.$win, 'resize', 'resize');

                        this.initChart();

                        this.draw();

                        if (typeof(this.data.onAfterInit) != 'undefined') {
                            this.data.onAfterInit();
                        }

                    }, this)
                });
        },

        addChartReadyListener: function() {
            google.visualization.events.addListener(this.chart, 'ready', $.proxy(function() {
                this.drawing = false;

                if (typeof(this.data.onAfterDraw) != 'undefined') {
                    this.data.onAfterDraw();
                }

            }, this));
        },

        initChart: function() {
            this.$graph.addClass(this.type);
        },

        draw: function() {
            if (!this.drawing) {
                this.drawing = true;

                if (this.dataTable && this.chartOptions) {

                    this.chart.draw(this.dataTable, this.chartOptions);
                }
            }
        },

        resize: function() {
            if (this.chart && this.dataTable && this.chartOptions) {
                this.draw(this.dataTable, this.chartOptions);
            }
        },
    });