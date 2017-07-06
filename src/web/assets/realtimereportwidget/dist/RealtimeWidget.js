/**
 * Realtime
 */
Analytics.Realtime = Garnish.Base.extend(
{
    $element: null,
    $title: null,
    $body: null,
    $error: null,
    $activeUsers: null,
    $realtimeVisitors: null,
    $pageviewsChart: null,
    $activePagesTable: null,
    $activePagesTableBody: null,
    $activePagesTableBodyNoData: null,

    chart: null,
    chartData: null,
    chartOptions: null,
    timer: false,
    settings: null,

    init: function(element, settings)
    {
        this.setSettings(settings);

        this.$element = $('#'+element);
        this.$title = $('.title', this.$element);
        this.$body = $('.body', this.$element);
        this.$error = $('.error', this.$element);

        this.$realtimeVisitors = $('.analytics-realtime-visitors', this.$element);

        this.$activeUsers = $('.active-users', this.$realtimeVisitors);

        this.$pageviewsChart = $('.pageviews .chart', this.$element);
        this.$pageviewsNoData = $('.pageviews .nodata', this.$element);

        this.$activePagesTable = $('.active-pages table', this.$element);
        this.$activePagesTableBody = $('.active-pages table tbody', this.$element);
        this.$activePagesNoData = $('.active-pages .nodata', this.$element);

        this.addListener(Garnish.$win, 'resize', '_handleWindowResize');

        this.loadGoogleCharts();
        
        this.start();
    },

    start: function()
    {
        if(this.timer)
        {
            this.stop();
        }

        this.request();

        this.timer = setInterval($.proxy(function()
        {
            this.request();

        }, this), this.settings.refreshInterval * 1000);
    },

    stop: function()
    {
        clearInterval(this.timer);
    },

    request: function()
    {
        var data = {
            viewId: this.settings.viewId
        };

        Craft.queueActionRequest('analytics/reports/realtime-widget', data, $.proxy(function(response, textStatus)
        {
            if(textStatus == 'success' && typeof(response.error) == 'undefined')
            {
                this.$error.addClass('hidden');
                this.$realtimeVisitors.removeClass('hidden');
                this.handleResponse(response);
            }
            else
            {
                var msg = 'An unknown error occured.';

                if(typeof(response) != 'undefined' && response && typeof(response.error) != 'undefined')
                {
                    msg = response.error;
                }

                this.$realtimeVisitors.addClass('hidden');
                this.$error.html(msg);
                this.$error.removeClass('hidden');
            }

        }, this));
    },

    handleResponse: function(response)
    {
        this.$activeUsers.text(response.activeUsers);


        // Page views
        this.setChart(response);


        // Active pages

        this.$activePagesTableBody.empty();

        if(response.activePages.rows && response.activePages.rows.length > 0) {
            this.$activePagesNoData.addClass('hidden');
            $.each(response.activePages.rows, $.proxy(function(key, row) {
                var $tr = $('<tr></tr>').appendTo(this.$activePagesTableBody);
                $('<td>'+row[0]+'</td>').appendTo($tr);
                $('<td>'+row[1]+'</td>').appendTo($tr);
            }, this));
            this.$activePagesTable.removeClass('hidden');
        } else {
            this.$activePagesTable.addClass('hidden');
            this.$activePagesNoData.removeClass('hidden');
        }
    },

    loadGoogleCharts: function()
    {
        google.charts.load('current', {packages: ['corechart', 'bar']});

        /*

        google.charts.setOnLoadCallback($.proxy(function() {
            this._setChart();
        }, this));

        */
    },

    setChart: function(response)
    {
        var data = new google.visualization.DataTable();
        data.addColumn('number', 'Minutes ago');
        data.addColumn('number', 'Pageviews');

        var pageviews = response.pageviews;

        if(pageviews.rows && pageviews.rows.length > 0) {
            this.$pageviewsChart.removeClass('hidden');
            this.$pageviewsNoData.addClass('hidden');
        } else {
            this.$pageviewsChart.addClass('hidden');
            this.$pageviewsNoData.removeClass('hidden');
        }

        for(minutesAgo = 30; minutesAgo >= 0; minutesAgo--) {
            var rowPageviews = 0;
            $.each(pageviews.rows, function(key, row) {
                var rowMinutesAgo = parseInt(row[0]);

                if(rowMinutesAgo === minutesAgo) {
                    rowPageviews = parseInt(row[1]);
                }
            });

            data.addRow([{v: minutesAgo, f: minutesAgo+" minutes ago"}, rowPageviews]);
        }

        var options = {
            height: 150,
            //backgroundColor: '#eee',
            theme: 'maximized',
            bar: {groupWidth: "90%"},
            legend: {
                position: 'bottom',
            },
            hAxis: {
                direction: -1,
                baselineColor: 'transparent',
                gridlineColor: 'transparent',
                textPosition: 'none',
                gridlines: {
                    count: 0
                },
            },
            vAxis: {
                baselineColor: '#fff',
                gridlineColor: '#fff',
                textPosition: 'none',
                gridlines: {
                    count: 0
                },
            }
        };

        this.chartData = data;
        this.chartOptions = options;

        this.chart = new google.visualization.ColumnChart(this.$pageviewsChart.get(0));
        this.chart.draw(this.chartData, this.chartOptions);
    },

    _handleWindowResize: function()
    {
        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
        }
    },
}, {
    defaults: {
        viewId: null,
        refreshInterval: 15,
    }
});
