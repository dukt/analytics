google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsCountReport = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $('#'+element);
        this.$inject = $('.analytics-inject', this.$element);
        this.$error = $('.analytics-error', this.$element);
        this.$errorElement = $('.error', this.$element);

        var data = {
            start: this.$element.data('start'),
            end: this.$element.data('end')
        };

        this.$element.addClass('analytics-loading');

        if(typeof(google.visualization) == 'undefined')
        {
            this.$errorElement.html("No internet connection");
            this.$element.addClass('error');
            return false;
        }

        Craft.queueActionRequest('analytics/getCountReport', data, $.proxy(function(response) {

            if(typeof(response.error) != 'undefined') {
                // $('.inject', this.$error).html(response.error);
                // this.$error.removeClass('hidden');
                // $(this.$element).addClass('error');
                this.$errorElement.html(response.error);
                this.$element.addClass('error');
            } else {
                this.$inject.html(response.html);
            }

            this.$element.removeClass('analytics-loading');
        }, this));
    },
});

AnalyticsReport = Garnish.Base.extend({
    init: function(element)
    {
        this.$resizeTimer = false;

        this.$element = $("#"+element);
        this.$errorElement = $('.error', this.$element);
        $id = this.$element.data('widget-id');
        this.$reportElements = $('.analyticsTab', this.$element);
        this.$charts = [];
        this.$chartData = [];

        if(typeof(google.visualization) == 'undefined')
        {
            this.$errorElement.html("No internet connection");
            this.$element.addClass('error');
            return false;
        }

        // console.log('widget id', element, $id);

        // tabs

        $('.analytics-tabs .analytics-nav a', this.$element).each($.proxy(function (k2, v2) {

            $(v2).click($.proxy(function (link, e) {

                $('.analytics-nav a', this.$element).removeClass('active');

                $(link).addClass('active');

                $('.analyticsTab', this.$element).addClass('hidden');

                $($('.analyticsTab', this.$element).get(k2)).removeClass('hidden');


                // redraw the chart
                this.$element.trigger('redraw');

                // $(window).trigger('resize');

                return false;
            }, this, v2));

         }, this));


        $('.analytics-tabs .analytics-nav li:first-child a', this.$element).trigger('click');



        // show more

        $('a.more, a.less', this.$element).click($.proxy(function (e) {

            var moreDiv = $(e.target).parents('div.more');

            if($('div.more-content', moreDiv).hasClass('hidden')) {
                $('div.more-content', moreDiv).removeClass('hidden');
                $('a.more', moreDiv).addClass('hidden');
            } else {
                $('div.more-content', moreDiv).addClass('hidden');
                $('a.more', moreDiv).removeClass('hidden');
            }

            // redraw the chart
            this.$element.trigger('redraw');


            return false;
        }, this));


        // request

        Craft.queueActionRequest('analytics/report', { id: $id }, $.proxy(function(response) {

            if(typeof(response.error) != 'undefined')
            {
                // handle error
                this.$errorElement.html(response.error);
                this.$element.addClass('error');
            }
            else
            {
                $reports = response.reports;
                $reportElements = this.$reportElements;

                $.each($reports, $.proxy(function (k, report) {

                    var pieElement = $('.chart.piechart', $reportElements[k]).get(0);
                    var geoElement = $('.chart.geochart', $reportElements[k]).get(0);
                    var areaElement = $('.chart.areachart', $reportElements[k]).get(0);
                    var tableElement = $('.chart.table', $reportElements[k]).get(0);
                    this.$chartData[k] = new google.visualization.DataTable();

                    var columns = AnalyticsUtils.getColumns(report);
                    $.each(columns, $.proxy(function (k2, column) {
                        this.$chartData[k].addColumn(column.type, column.name);
                    }, this));

                    var rows = AnalyticsUtils.getRows(report);

                    this.$chartData[k].addRows(rows);

                    if(typeof(this.$charts[k]) == 'undefined')
                    {
                        this.$charts[k] = [];
                    }

                    if(pieElement)
                    {
                        this.$charts[k]['chart'] = AnalyticsUtils.getChart(pieElement, 'PieChart', report.options);
                    }
                    else if(geoElement)
                    {
                        this.$charts[k]['chart'] = AnalyticsUtils.getChart(geoElement, 'GeoChart', report.options);
                    }
                    else if(areaElement)
                    {
                        this.$charts[k]['chart'] = AnalyticsUtils.getChart(areaElement, 'AreaChart', report.options);
                    }

                    if(tableElement)
                    {
                        this.$charts[k]['table'] = AnalyticsUtils.getChart(tableElement, 'Table', report.options);
                    }

                    if(typeof(this.$charts[k]) != 'undefined')
                    {
                        this.redraw();
                    }

                    this.$element.bind('redraw', $.proxy(function () {
                        clearTimeout(this.$resizeTimer);
                        this.$resizeTimer = setTimeout($.proxy(function () {
                            this.redraw();
                        }, this), 1);

                    }, this));

                    $(window).resize($.proxy(function () {
                        clearTimeout(this.$resizeTimer);
                        this.$resizeTimer = setTimeout($.proxy(function () {
                            this.redraw();
                        }, this), 1);
                    }, this));

                }, this));
            }

        }, this));
    },

    redraw: function() {
        var countElements = this.$reportElements.length;
        for(i=0; i < countElements; i++)
        {
            if(typeof(this.$charts[i]) != 'undefined')
            {

                if(typeof(this.$charts[i]['chart']) != 'undefined')
                {
                    this.$charts[i]['chart'].chart.draw(this.$chartData[i], this.$charts[i]['chart'].chartOptions);
                }

                if(typeof(this.$charts[i]['table']) != 'undefined')
                {
                    this.$charts[i]['table'].chart.draw(this.$chartData[i], this.$charts[i]['table'].chartOptions);
                }
            }
        }
    }
});

