AnalyticsField = Garnish.Base.extend({

    init: function(fieldId)
    {
        this.initGoogleVisualization($.proxy(function() {

            // Google Visualization is loaded and ready

            this.initField(fieldId);

        }, this));
    },

    initGoogleVisualization: function(onGoogleVisualizationLoaded)
    {
        if(Analytics.GoogleVisualisationCalled == false)
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", { packages:['corechart'], 'language': AnalyticsChartLanguage });

            Analytics.GoogleVisualisationCalled = true;
        }

        google.setOnLoadCallback($.proxy(function() {

            if(typeof(google.visualization) == 'undefined')
            {
                // No Internet ?

                // this.$widget.addClass('hidden');
                // this.$error.html('An unknown error occured');
                // this.$error.removeClass('hidden');

                return;
            }
            else
            {
                onGoogleVisualizationLoaded();
            }
        }, this));
    },

    initField: function(fieldId)
    {
        // elements
        this.$element = $("#"+fieldId);
        this.$field = $(".analytics-field", this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$errorElement = $('.error', this.$element);
        this.$metricElement = $('.analytics-metric select', this.$element);
        this.$chartElement = $('.chart', this.$element);

        // variables
        this.elementId = $('.analytics-field', this.$element).data('element-id');
        this.locale = $('.analytics-field', this.$element).data('locale');
        this.chart = false;
        this.chartData = false;

        // listeners
        this.addListener(Garnish.$win, 'resize', 'resize');
        this.addListener(this.$metricElement, 'change', 'onMetricChange');

        this.$metricElement.trigger('change');
    },

    onMetricChange: function(ev)
    {
        this.$metric = $(ev.currentTarget).val();
        this.request();
    },

    request: function()
    {
        this.chartData = new google.visualization.DataTable();

        this.$spinner.removeClass('hidden');

        Craft.postActionRequest('analytics/reports/getElementReport', { elementId: this.elementId, locale: this.locale, metric: this.$metric }, $.proxy(function(response) {

            this.$spinner.addClass('hidden');

            if(typeof(response.error) != 'undefined')
            {
                this.$errorElement.html(response.error);
                this.$field.addClass('analytics-error');
            }
            else
            {
                this.$field.removeClass('analytics-error');

                this.chartData = Analytics.Utils.responseToDataTable(response.data);

                this.chart = new google.visualization.AreaChart(this.$chartElement.get(0));

                if(typeof(this.chart) != 'undefined')
                {
                    this.chart.draw(this.chartData, this.chartOptions);
                }
            }

        }, this));
    },

    resize: function()
    {
        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
        }
    },

    chartOptions: Analytics.ChartOptions.field()
});
