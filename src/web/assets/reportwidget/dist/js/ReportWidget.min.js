(function($) {
    /** global: Analytics */
    /**
     * Report Widget
     */
    Analytics.ReportWidget = Garnish.Base.extend(
        {
            settings: null,
            report: null,
            localeDefinition: null,
            chartLanguage: null,

            $title: null,
            $body: null,
            $date: null,
            $spinner: null,
            $error: null,
            $report: null,

            init: function(element, settings) {
                this.setSettings(settings, Analytics.ReportWidget.defaults);
                this.localeDefinition = Analytics.Utils.getLocaleDefinition(this.settings.currencyDefinition);

                // Set elements
                this.$element = $('#' + element);
                this.$title = $('.title', this.$element);
                this.$body = $('.body', this.$element);
                this.$date = $('.date', this.$element);
                this.$spinner = $('.spinner', this.$element);
                this.$spinner.removeClass('body-loading');
                this.$error = $('.error', this.$element);

                // Resize the chart once so that it has the right dimensions
                $(this.$element).resize(function() {
                    if (this.report) {
                        this.report.resize();
                    }
                }.bind(this));

                // Chart language
                this.chartLanguage = settings['chartLanguage'];

                // Send the request or use the cached response
                var request;

                if (typeof (settings['request']) != 'undefined') {
                    request = settings['request'];
                }

                if (typeof (settings['cachedResponse']) != 'undefined' && settings['cachedResponse']) {
                    this.$spinner.removeClass('hidden');

                    var response = settings['cachedResponse'];

                    this.parseResponse(response);
                } else if (request) {
                    this.sendRequest(request);
                }
            },

            sendRequest: function(data) {
                this.$spinner.removeClass('hidden');

                $('.report', this.$body).remove();

                this.$error.addClass('hidden');

                Craft.postActionRequest('analytics/reports/report-widget', data, $.proxy(function(response, textStatus) {
                    if (textStatus == 'success' && typeof (response.error) == 'undefined') {
                        this.parseResponse(response);
                    } else {
                        var msg = 'An unknown error occured.';

                        if (typeof (response) != 'undefined' && response && typeof (response.error) != 'undefined') {
                            msg = response.error;
                        }

                        this.$error.html(msg);
                        this.$error.removeClass('hidden');
                        this.$spinner.addClass('hidden');
                    }

                    if (window.dashboard && window.dashboard.grid) {
                        window.dashboard.grid.refreshCols(true);
                    }
                }, this));
            },

            parseResponse: function(response) {
                var chartData = response,
                    type = response.type,
                    chartType = type.charAt(0).toUpperCase() + type.slice(1);

                this.$report = $('<div class="report"></div>');
                this.$report.appendTo(this.$body);

                chartData['onAfterDraw'] = $.proxy(function() {
                    this.$spinner.addClass('hidden');
                }, this);

                this.report = new Analytics.reports[chartType](this.$report, chartData, this.localeDefinition, this.chartLanguage);
            }
        },
        {
            defaults: {
                chartLanguage: null,
                request: null,
                cachedResponse: null,
                currencyDefinition: null,
            }
        });
})(jQuery);