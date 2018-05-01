(function($) {
    /** global: Analytics */
    /**
     * E-commerce Widget
     */
    Analytics.EcommerceWidget = Garnish.Base.extend(
        {
            settings: null,
            requestData: null,

            $chart: null,

            init: function(element, settings)
            {
                this.setSettings(settings, Analytics.EcommerceWidget.defaults);

                this.$element = $('#'+element);
                this.$body = $('.body', this.$element);
                this.$spinner = $('.spinner', this.$element);
                this.$spinner.removeClass('body-loading');
                this.$error = $('<div class="error" />').appendTo(this.$body);

                this.$period = $('.period', this.$element);
                this.$revenue = $('.revenue', this.$element);
                this.$revenuePerTransaction = $('.revenue-per-transaction', this.$element);
                this.$transactions = $('.transactions', this.$element);
                this.$transactionsPerSession = $('.transactions-per-session', this.$element);
                this.$chart = $('.chart', this.$element);

                this.sendRequest();
            },

            sendRequest: function()
            {
                this.$spinner.removeClass('hidden');

                this.$error.addClass('hidden');

                var data = {
                    viewId: this.settings.viewId,
                    period: this.settings.period
                };

                Craft.postActionRequest('analytics/reports/ecommerce-widget', data, $.proxy(function(response, textStatus)
                {
                    this.$spinner.addClass('hidden');

                    if(textStatus == 'success' && typeof(response.error) == 'undefined')
                    {
                        this.parseResponse(response);
                    }
                    else
                    {
                        var msg = 'An unknown error occured.';

                        if(typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined')
                        {
                            msg = response.error;
                        }

                        this.$error.html(msg);
                        this.$error.removeClass('hidden');
                    }

                }, this));
            },

            parseResponse: function(response)
            {
                var localeDefinition = this.settings.localeDefinition;

                var totalRevenue = Analytics.Utils.formatByType(localeDefinition, 'currency', response.totalRevenue);
                var totalRevenuePerTransaction = Analytics.Utils.formatByType(localeDefinition, 'currency', response.totalRevenuePerTransaction);
                var totalTransactions = Analytics.Utils.formatByType(localeDefinition, 'number', response.totalTransactions);
                var totalTransactionsPerSession = Analytics.Utils.formatByType(localeDefinition, 'percent', response.totalTransactionsPerSession);

                this.$period.html(response.period);
                this.$revenue.html(totalRevenue);
                this.$revenuePerTransaction.html(totalRevenuePerTransaction);
                this.$transactions.html(totalTransactions);
                this.$transactionsPerSession.html(totalTransactionsPerSession);

                this.chart = new Analytics.reports.Area(this.$chart, response.reportData, localeDefinition, this.settings.chartLanguage);
            },
        },
        {
            defaults: {
                viewId: null,
                period: null,
                localeDefinition: null,
                chartLanguage: null,
            }
        });
})(jQuery);