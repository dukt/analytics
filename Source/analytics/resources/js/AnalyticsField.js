/**
 * Field
 */
 AnalyticsField = Garnish.Base.extend({

    init: function(fieldId, options)
    {
        this.$element = $("#"+fieldId);
        this.$field = $(".analytics-field", this.$element);
        this.$metric = $('.analytics-metric select', this.$element);
        this.$chart = $('.chart', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$error = $('.error', this.$element);

        this.elementId = $('.analytics-field', this.$element).data('element-id');
        this.locale = $('.analytics-field', this.$element).data('locale');
        this.metric = this.$metric.val();

        this.addListener(this.$metric, 'change', 'onMetricChange');

        if(typeof(options['cachedResponse']) != 'undefined')
        {
            this.parseResponse(options['cachedResponse']);
        }
        else
        {
            this.request();
        }
    },

    onMetricChange: function(ev)
    {
        this.metric = $(ev.currentTarget).val();
        this.request();
    },

    request: function()
    {
        this.$spinner.removeClass('hidden');
        this.$field.removeClass('analytics-error');

        var data = {
            elementId: this.elementId,
            locale: this.locale,
            metric: this.metric
        };

        Craft.postActionRequest('analytics/reports/getElementReport', data, $.proxy(function(response) {

            this.parseResponse(response);

        }, this));
    },

    parseResponse: function(response)
    {
        this.$spinner.addClass('hidden');

        if(typeof(response.error) != 'undefined')
        {
            this.$error.html(response.error);
            this.$field.addClass('analytics-error');
        }
        else
        {
            response.chartOptions = Analytics.ChartOptions.field();
            this.chart = new Analytics.Chart(this.$chart, response);
        }
    }
});
