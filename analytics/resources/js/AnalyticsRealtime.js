AnalyticsRealtimeReport = Garnish.Base.extend({
    init: function(element)
    {
        this._refresh = false;

        this.$element = $('#'+element);
        this.$realtime = $('.analytics-realtime:first', this.$element);
        this.$progress = $('.analytics-progress:first', this.$realtime);
        this.$legend = $('.analytics-legend:first', this.$realtime);
        this.$errors = $('.analytics-errors:first', this.$realtime);
        this.$errorsInject = $('.analytics-errors-inject:first', this.$realtime);
        this.$body = $('.analytics-body:first', this.$realtime);
        this.$progressBlue = $('.progress-bar.blue:first', this.$realtime);
        this.$progressGreen = $('.progress-bar.green:first', this.$realtime);
        this.$content = $('.analytics-realtime-content:first', this.$realtime);
        this.$sources = $('.analytics-realtime-sources:first', this.$realtime);
        this.$countries = $('.analytics-realtime-countries:first', this.$realtime);
        this.$noVisitors = $('.no-active-visitors:first', this.$realtime);
        this.$count = $('.active-visitors .count:first', this.$realtime);
        this.$spinner = $('.spinner:first', this.$realtime);

        this.$spinner.removeClass('body-loading');

        this.start();
    },

    start: function()
    {
        this.request();

        var refreshInterval = eval(Analytics.realtimeRefreshInterval);

        if(refreshInterval == null)
        {
            // Default to 10 seconds
            refreshInterval = 10;
        }
        else if(refreshInterval<2)
        {
            // mini 2 seconds
            refreshInterval = 100;
        }

        // to (ms)
        refreshInterval = refreshInterval * 1000;

        this._refresh = setInterval($.proxy(function() {
            this.request();
        }, this), refreshInterval);
    },

    stop: function()
    {
        clearInterval(this._refresh);
    },

    request: function()
    {
        console.log('request');
        this.$spinner.removeClass('hidden');

        Craft.postActionRequest('analytics/realtime', {}, $.proxy(function(response) {

            // realtime

            this.$errorsInject.html('');

            if(typeof(response.error) != 'undefined') {
                this.$errors.removeClass('hidden');

                this.$body.addClass('hidden');

                $('<p class="error">'+response.error.message+'</p>').appendTo(this.$errorsInject);
            } else {

                this.$errors.addClass('hidden');
                this.$body.removeClass('hidden');

                this.updateProgress(response);
                this.updateTables(response);
            }

            this.$spinner.addClass('hidden');

        }, this));
    },

    updateProgress: function(response)
    {
        var newVisitor = response.visitorType.newVisitor;
        var returningVisitor = response.visitorType.returningVisitor;
        var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

        this.$count.text(calcTotal);

        if (calcTotal > 0)
        {
            this.$progress.removeClass('hidden');
            this.$legend.removeClass('hidden');
        }
        else
        {
            this.$progress.addClass('hidden');
            this.$legend.addClass('hidden');
        }

        if(calcTotal > 0)
        {
            var blue = Math.round(100 * newVisitor / calcTotal);
        }
        else
        {
            var blue = 100;
        }

        var green = 100 - blue;


        // blue

        this.$progressBlue.css('width', blue+'%');
        $('span', this.$progressBlue).text(blue+'%');

        if(blue > 0)
        {
            this.$progressBlue.removeClass('hidden');
        }
        else
        {
            this.$progressBlue.addClass('hidden');
        }

        // green

        this.$progressGreen.css('width', green+'%');
        $('span', this.$progressGreen).text(green+'%');

        if(green > 0)
        {
            this.$progressGreen.removeClass('hidden');
        }
        else
        {
            this.$progressGreen.addClass('hidden');
        }
    },

    updateTables: function(response)
    {
        var newVisitor = response.visitorType.newVisitor;
        var returningVisitor = response.visitorType.returningVisitor;
        var calcTotal = ((returningVisitor * 1) + (newVisitor * 1));

        if (calcTotal > 0)
        {
            this.$noVisitors.addClass('hidden');

            // content

            $('table', this.$content).removeClass('hidden');
            $('tbody', this.$content).html('');

            $.each(response.content, function(k,v) {
                var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

                $('tbody', this.$content).append(row);
            });

            // sources

            $('table', this.$sources).removeClass('hidden');
            $('tbody', this.$sources).html('');

            $.each(response.sources, function(k,v) {
                var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

                $('tbody', this.$sources).append(row);
            });

            // countries

            $('table', this.$countries).removeClass('hidden');
            $('tbody', this.$countries).html('');

            $.each(response.countries, function(k,v) {
                var row = $('<tr><td>'+k+'</td><td class="thin">'+v+'</td></td>');

                $('tbody', this.$countries).append(row);
            });
        }
        else
        {
            this.$noVisitors.removeClass('hidden');
            $('table', this.$content).addClass('hidden');
            $('table', this.$sources).addClass('hidden');
            $('table', this.$countries).addClass('hidden');
        }
    }
});
