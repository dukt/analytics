/** global: Analytics */

AnalyticsReportField = Garnish.Base.extend({
    localeDefinition: null,

    init: function(fieldId, settings) {
        this.setSettings(settings, AnalyticsReportField.defaults);
        this.localeDefinition = Analytics.Utils.getLocaleDefinition(this.settings.currencyDefinition);

        // Set elements
        this.$element = $("#" + fieldId);
        this.$field = $(".analytics-field", this.$element);
        this.$metric = $('.analytics-metric select', this.$element);
        this.$chart = $('.chart', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$error = $('.error', this.$element);

        this.elementId = $('.analytics-field', this.$element).data('element-id');
        this.siteId = $('.analytics-field', this.$element).data('site-id');
        this.metric = this.$metric.val();

        this.addListener(this.$metric, 'change', 'onMetricChange');

        // Send the request or use the cached response
        if (!this.settings.cachedResponse) {
            this.sendRequest();
        }
        else {
            this.parseResponse(this.settings.cachedResponse);
        }
    },

    onMetricChange: function(ev) {
        this.metric = $(ev.currentTarget).val();
        this.sendRequest();
    },

    sendRequest: function() {
        this.$spinner.removeClass('hidden');
        this.$field.removeClass('analytics-error');

        var data = {
            elementId: this.elementId,
            siteId: this.siteId,
            metric: this.metric
        };

        Craft.postActionRequest('analytics/reports/element', data, $.proxy(function(response, textStatus) {
            if (textStatus == 'success' && typeof(response.error) == 'undefined') {
                this.parseResponse(response);
            }
            else {
                var msg = Craft.t('An unknown error occurred.');

                if (typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined') {
                    msg = response.error;
                }

                this.$error.html(msg);
                this.$error.removeClass('hidden');

                this.$field.addClass('analytics-error');
            }

            this.$spinner.addClass('hidden');

        }, this));
    },

    parseResponse: function(response) {
        Garnish.requestAnimationFrame($.proxy(function() {
            response.chartOptions = Analytics.ChartOptions.field();
            this.chart = new Analytics.reports.Area(this.$chart, response, this.localeDefinition, this.settings.chartLanguage);
        }, this));
    }
}, {
    defaults: {
        cachedResponse: null,
        currencyDefinition: null,
    }
});