google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

AnalyticsField = Garnish.Base.extend({
    init: function(fieldId)
    {
        console.log('fieldId', fieldId);

        this.$field = $("#"+fieldId);
        this.$metricElement = $('.analytics-metric select', this.$field);
        this.$chartElement = $('.chart', this.$field);
        this.$elementId = $('.analytics-field', this.$field).data('element-id');
        this.$chart = false;

        $('#'+fieldId+' .heading').addClass('hidden');

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

        Craft.postActionRequest('analytics/elementReport', { id: this.$elementId, metric: this.$metric }, function(response) {

            $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {

                $type = 'string';

                if(columnHeader.name == 'ga:date') {
                    $type = 'date';
                }
                else
                {
                    if(columnHeader.dataType == 'INTEGER'
                        || columnHeader.dataType == 'PERCENT'
                        || columnHeader.dataType == 'TIME')
                    {
                        $type = 'number';
                    }
                }

                console.log('header', $type, columnHeader.name);

                chartData.addColumn($type, columnHeader.name);

            });

            $.each(response.apiResponse.rows, function(k, row) {

                $.each(response.apiResponse.columnHeaders, function(k2, columnHeader) {

                    if(columnHeader.name == 'ga:date')
                    {
                        $date = response.apiResponse.rows[k][k2];
                        $year = eval($date.substr(0, 4));
                        $month = eval($date.substr(4, 2)) - 1;
                        $day = eval($date.substr(6, 2));

                        newDate = new Date($year, $month, $day);

                        response.apiResponse.rows[k][k2] = newDate;
                    }
                    else
                    {
                        if(columnHeader.dataType == 'INTEGER')
                        {
                            response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                        }
                        else if(columnHeader.dataType == 'PERCENT')
                        {
                            response.apiResponse.rows[k][k2] = {
                                'f': (Math.round(eval(response.apiResponse.rows[k][k2]) * 100) / 100)+" %",
                                'v': eval(response.apiResponse.rows[k][k2])
                            };
                        }
                        else if(columnHeader.dataType == 'TIME')
                        {
                            response.apiResponse.rows[k][k2] = {
                                'f' : eval(response.apiResponse.rows[k][k2])+" seconds",
                                'v' : eval(response.apiResponse.rows[k][k2]),
                            };
                        }
                    }
                });


            });

            console.log(response);

            chartData.addRows(response.apiResponse.rows);

            options = {
                areaOpacity: 0.1,
                pointSize: 8,
                lineWidth: 4,
                legend: false,
                hAxis: {
                    baselineColor: '#fff',
                    gridlines: {
                        color: 'none'
                    }
                },
                series:{
                    0:{targetAxisIndex:0},
                    1:{targetAxisIndex:1}
                },
                vAxes: [
                    {
                        format: '#',
                        textPosition: 'in',
                        baselineColor: '#ccc',
                        gridlines: {
                            color: '#eee'
                        }
                    },
                    {
                        format: '#',
                        textPosition: 'in',
                        baselineColor: '#ccc',
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

        }, this);
    }
});

