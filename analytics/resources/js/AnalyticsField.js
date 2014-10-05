// google visualisation
if(typeof(google.visualization) == 'undefined')
{
    if(typeof(AnalyticsChartLanguage) == 'undefined')
    {
        AnalyticsChartLanguage = 'en';
    }

    google.load("visualization", "1", {packages:['corechart'], 'language': AnalyticsChartLanguage});
}

AnalyticsField = Garnish.Base.extend({
    init: function(fieldId)
    {
        // elements
        this.$element = $("#"+fieldId);
        this.$field = $(".analytics-field", this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$errorElement = $('.error', this.$element);
        this.$metricElement = $('.analytics-metric select', this.$element);
        this.$chartElement = $('.chart', this.$element);

        // variables
        this.elementId = $('.analytics-field', this.$element).data('element-id');
        this.locale = $('.analytics-field', this.$element).data('locale');
        this.chart = false;
        this.chartData = false;

        // listeners
        this.addListener(Garnish.$win, 'resize', 'resize');
        this.addListener(this.$metricElement, 'change', 'onMetricChange');

        this.$metricElement.trigger('change');
    },

    onMetricChange: function(ev)
    {
        this.$metric = $(ev.currentTarget).val();
        this.request();
    },

    request: function()
    {
        this.chartData = new google.visualization.DataTable();

        this.$spinner.removeClass('hidden');

        Craft.postActionRequest('analytics/explorer/elementReport', { elementId: this.elementId, locale: this.locale, metric: this.$metric }, $.proxy(function(response) {

            this.$spinner.addClass('hidden');

            if(typeof(response.error) != 'undefined')
            {
                this.$errorElement.html(response.error);
                this.$field.addClass('analytics-error');
            }
            else
            {
                var apiData = response.data;

                this.$field.removeClass('analytics-error');

                $.each(apiData.columns, $.proxy(function(k, apiColumn)
                {
                    var column = AnalyticsUtils.parseColumn(apiColumn);
                    this.chartData.addColumn(column.type, column.label);
                }, this));

                rows = AnalyticsUtils.parseRows(apiData.columns, apiData.rows);

                this.chartData.addRows(rows);

                this.chart = new google.visualization.AreaChart(this.$chartElement.get(0));

                if(typeof(this.chart) != 'undefined')
                {
                    this.chart.draw(this.chartData, this.chartOptions);
                }
            }

        }, this));
    },

    resize: function()
    {
        if(this.chart)
        {
            this.chart.draw(this.chartData, this.chartOptions);
        }
    },

    chartOptions: {
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
    }
});

