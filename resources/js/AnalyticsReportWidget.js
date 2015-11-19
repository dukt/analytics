/**
 * Chart Widget
 */
Analytics.ReportWidget = Garnish.Base.extend(
{
    requestData: null,

    $grid: null,

    init: function(element, options)
    {
        this.$element = $('#'+element);
        this.$title = $('.title', this.$element);
        this.$body = $('.body', this.$element);
        this.$date = $('.date', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$spinner.removeClass('body-loading');
        this.$error = $('.error', this.$element);

        Garnish.$doc.ready($.proxy(function() {
            this.$grid = $('#main > .grid');
        }, this));


        // default/cached request

        this.chartRequest = options['request'];

        if(typeof(this.chartRequest) != 'undefined')
        {
            this.requestData = this.chartRequest;
        }


        // default/cached response

        this.chartResponse = options['cachedResponse'];

        if(typeof(this.chartResponse) != 'undefined')
        {
            this.parseResponse(this.chartResponse);
        }
        else if(this.requestData)
        {
            this.chartResponse = this.sendRequest(this.requestData);
        }
    },

    sendRequest: function(data)
    {
        this.$spinner.removeClass('hidden');

        $('.chart', this.$body).remove();

        this.$error.addClass('hidden');

        Craft.postActionRequest('analytics/reports/getChartReport', data, $.proxy(function(response, textStatus)
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
        this.$spinner.addClass('hidden');

        $chart = $('<div class="chart"></div>');
        $chart.appendTo(this.$body);

        this.$title.html(response.metric);
        this.$date.html(response.periodLabel);

        this.chart = new Analytics.Chart($chart, response);
    }
});
