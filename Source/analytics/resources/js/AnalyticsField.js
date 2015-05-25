AnalyticsField = Garnish.Base.extend({

    init: function(fieldId)
    {
        if(typeof(google.visualization) == 'undefined')
        {
            if(typeof(AnalyticsChartLanguage) == 'undefined')
            {
                AnalyticsChartLanguage = 'en';
            }

            google.load("visualization", "1", {
                packages:['corechart'],
                language: AnalyticsChartLanguage,
                callback: $.proxy(this, 'initField', fieldId)
            });
        }
        else
        {
            this.initField(fieldId);
        }
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

        Craft.postActionRequest('analytics/explorer/elementReport', { elementId: this.elementId, locale: this.locale, metric: this.$metric }, $.proxy(function(response) {

            this.$spinner.addClass('hidden');

            if(typeof(response.error) != 'undefined')
            {
                this.$errorElement.html(response.error);
                this.$field.addClass('analytics-error');
            }
            else
            {
                this.$field.removeClass('analytics-error');

                this.chartData = AnalyticsUtils.responseToDataTable(response.data);

                // $.each(apiData.columns, $.proxy(function(k, apiColumn)
                // {
                //     var column = AnalyticsUtils.parseColumn(apiColumn);
                //     this.chartData.addColumn(column.type, column.label);
                // }, this));

                // rows = AnalyticsUtils.parseRows(apiData.columns, apiData.rows);

                // this.chartData.addRows(rows);

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

    chartOptions: AnalyticsChartOptions.field()
});

