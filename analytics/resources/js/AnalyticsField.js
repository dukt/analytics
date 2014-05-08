google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsField = Garnish.Base.extend({
    init: function(fieldId)
    {
        this.$element = $("#"+fieldId);
        this.$field = $(".analytics-field", this.$element);
        this.$spinner = $('.spinner', this.$element);

        this.$errorElement = $('.error', this.$element);

        this.$metricElement = $('.analytics-metric select', this.$element);
        this.$chartElement = $('.chart', this.$element);
        this.$elementId = $('.analytics-field', this.$element).data('element-id');
        this.$chart = false;

        // $('#'+fieldId+' .heading').addClass('hidden');

        var $this = this;

        this.$metricElement.change(function() {

            $this.$metric = $(this).val();

            $this.request();
        });

        this.$metricElement.trigger('change');
    },

    request: function()
    {
        var chartData = new google.visualization.DataTable();
        var options = {};

        this.$spinner.removeClass('hidden');

        Craft.postActionRequest('analytics/elementReport', { id: this.$elementId, metric: this.$metric }, $.proxy(function(response) {

            this.$spinner.addClass('hidden');

            if(typeof(response.error) != 'undefined')
            {
                this.$errorElement.html(response.error);
                this.$field.addClass('analytics-error');
            }
            else
            {
                this.$field.removeClass('analytics-error');

                var columns = AnalyticsUtils.getColumns(response);
                $.each(columns, function(k, column) {
                    console.log(column.type, column.name);
                    chartData.addColumn(column.type, column.name);
                });

                var rows = AnalyticsUtils.getRows(response);
                chartData.addRows(rows);

                options = {
                    colors: ['#058DC7'],
                    backgroundColor: '#fdfdfd',
                    areaOpacity: 0.1,
                    pointSize: 8,
                    lineWidth: 4,
                    legend: false,
                    hAxis: {
                        textStyle: { color: '#888' },
                        baselineColor: '#fdfdfd',
                        gridlines: {
                            color: 'none',
                            count:2
                        }
                    },
                    vAxis:{
                        maxValue: 5,
                    },
                    series:{
                        0:{targetAxisIndex:0},
                        1:{targetAxisIndex:1}
                    },
                    vAxes: [
                        {
                            textStyle: { color: '#888' },
                            format: '#',
                            textPosition: 'in',
                            baselineColor: '#eee',
                            gridlines: {
                                color: '#eee'
                            }
                        },
                        {
                            textStyle: { color: '#888' },
                            format: '#',
                            textPosition: 'in',
                            baselineColor: '#eee',
                            gridlines: {
                                color: '#eee'
                            }
                        }
                    ],
                    chartArea:{
                        top:10,
                        bottom:10,
                        width:"100%",
                        height:"80%"
                    }
                };

                this.$chart = new google.visualization.AreaChart(this.$chartElement.get(0));

                if(typeof(this.$chart) != 'undefined')
                {
                    this.$chart.draw(chartData, options);
                }

                var $this = this;

                $(window).resize(function() {
                    $this.$chart.draw(chartData, options);
                });
            }

        }, this));
    }
});

