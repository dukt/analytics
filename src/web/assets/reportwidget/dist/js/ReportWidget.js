/** global: Analytics */

/**
 * Report Widget
 */
Analytics.ReportWidget = Garnish.Base.extend(
    {
        report: null,
        localeDefinition: null,
        chartLanguage: null,

        $title: null,
        $body: null,
        $date: null,
        $spinner: null,
        $error: null,
        $report: null,

        init: function(element, options) {
            this.$element = $('#' + element);
            this.$title = $('.title', this.$element);
            this.$body = $('.body', this.$element);
            this.$date = $('.date', this.$element);
            this.$spinner = $('.spinner', this.$element);
            this.$spinner.removeClass('body-loading');
            this.$error = $('.error', this.$element);

            $(this.$element).resize(function() {
                if (this.report) {
                    this.report.resize();
                }
            }.bind(this));

            // locale definition

            this.localeDefinition = options['localeDefinition'];


            // chart language

            this.chartLanguage = options['chartLanguage'];


            // cached request

            var request;

            if (typeof(options['request']) != 'undefined') {
                request = options['request'];
            }


            // default/cached response

            if (typeof(options['cachedResponse']) != 'undefined' && options['cachedResponse']) {
                this.$spinner.removeClass('hidden');

                var response = options['cachedResponse'];

                this.parseResponse(response);
            }
            else if (request) {
                this.sendRequest(request);
            }
        },

        sendRequest: function(data) {
            this.$spinner.removeClass('hidden');

            $('.report', this.$body).remove();

            this.$error.addClass('hidden');

            Craft.postActionRequest('analytics/reports/report-widget', data, $.proxy(function(response, textStatus) {
                if (textStatus == 'success' && typeof(response.error) == 'undefined') {
                    this.parseResponse(response);
                }
                else {
                    var msg = 'An unknown error occured.';

                    if (typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined') {
                        msg = response.error;
                    }

                    this.$error.html(msg);
                    this.$error.removeClass('hidden');
                    this.$spinner.addClass('hidden');
                }

                window.dashboard.grid.refreshCols(true);

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
    });
