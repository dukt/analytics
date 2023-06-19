(function($) {
    /** global: Analytics */
    /**
     * E-commerce Widget
     */
    Analytics.EcommerceWidget = Garnish.Base.extend(
        {
            settings: null,
            requestData: null,
            localeDefinition: null,

            $chart: null,

            init: function(element, settings)
            {
                this.setSettings(settings, Analytics.EcommerceWidget.defaults);
                this.localeDefinition = Analytics.Utils.getLocaleDefinition(this.settings.currencyDefinition);

                // Set elements
                this.$element = $('#'+element);
                this.$body = $('.body', this.$element);
                this.$spinner = $('.da-spinner', this.$element);
                this.$error = $('<div class="error hidden" />').appendTo(this.$body);
                this.$period = $('.period', this.$element);
                this.$revenue = $('.revenue', this.$element);
                this.$revenuePerTransaction = $('.revenue-per-transaction', this.$element);
                this.$transactions = $('.transactions', this.$element);
                this.$transactionsPerSession = $('.transactions-per-session', this.$element);
                this.$chart = $('.chart', this.$element);
                this.$tiles = $('.tiles', this.$element);

                // Send the request
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
                        this.$tiles.removeClass('hidden');
                        this.$chart.removeClass('hidden');
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
                        this.$tiles.addClass('hidden');
                        this.$chart.addClass('hidden');
                    }

                }, this));
            },

            parseResponse: function(response)
            {
                var totalRevenue = Analytics.Utils.formatByType(this.localeDefinition, 'currency', response.totalRevenue);
                var totalRevenuePerTransaction = Analytics.Utils.formatByType(this.localeDefinition, 'currency', response.totalRevenuePerTransaction);
                var totalTransactions = Analytics.Utils.formatByType(this.localeDefinition, 'number', response.totalTransactions);
                var totalTransactionsPerSession = Analytics.Utils.formatByType(this.localeDefinition, 'percent', response.totalTransactionsPerSession);

                this.$period.html(response.period);
                this.$revenue.html(totalRevenue);
                this.$revenuePerTransaction.html(totalRevenuePerTransaction);
                this.$transactions.html(totalTransactions);
                this.$transactionsPerSession.html(totalTransactionsPerSession);

                this.chart = new Analytics.reports.Area(this.$chart, response.reportData, this.localeDefinition, this.settings.chartLanguage);
            },
        },
        {
            defaults: {
                viewId: null,
                period: null,
                currencyDefinition: null,
                chartLanguage: null,
            }
        });
})(jQuery);