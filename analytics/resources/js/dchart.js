var charts = {};

google.load("visualization", "1", {packages:["corechart", "table"]});

google.setOnLoadCallback(function() {

    // initialize all dcharts once ready

});

(function($){




    var Dchart = function(element, dataArray, jsonOptions)
    {
        console.log(dataArray);
        console.log('yo', dataArray);
        var elem = $(element);
        var obj = this;

        // Merge options with defaults
        var settings = $.extend(true, {
            options: {

            },
            chartQuery: {

            },
            chartOptions: {
                chartType: 'line',
                // title:'Default Title'
            }

        }, jsonOptions);

        // Public method

        var chart = false;
        var data = false;
        var chartOptions = false;

        this.drawChart = function() {
            chart.draw(data, chartOptions);
        };

        this.initChart = function()
        {
            data = google.visualization.arrayToDataTable(dataArray);
            console.log(settings.chartOptions.chartType);
            switch(settings.chartOptions.chartType) {
                case 'line':
                    chartOptions = $.extend({
                        // title: settings.chartOptions.title,
                        legend:{position:'bottom'},
                        chartArea : {
                            width:'100%',
                            left:50,
                            right:50,
                            top:50,
                            bottom:50

                        },
                        vAxis: {minValue: 4, format: '#'},
                        hAxis: {minValue: 4, format: '#', showTextEvery:5}
                    }, settings.chartOptions || {});

                    chart = new google.visualization.LineChart(element);

                    break;

                case 'column':

                    chartOptions = $.extend({
                        legend:'none',
                        sliceVisibilityThreshold:1/50,
                        pieHole:0.5,
                        // chartArea : {
                        //     width:'100%',
                        //     height: '100%',
                        //     top:0,
                        //     bottom:0
                        // },
                        vAxis: {textPosition:'in'},
                    }, settings.chartOptions || {});

                    console.log(chartOptions);
                    chart = new google.visualization.ColumnChart(element);

                    break;

                case 'donut':

                    chartOptions = $.extend(true, {
                        // legend:{position:'bottom'},
                        legend:'none',
                        sliceVisibilityThreshold:1/50,
                        pieHole:0.5,
                    }, settings.chartOptions);

                    chartOptions.chartArea.top = '5%';
                    chartOptions.chartArea.bottom = '5%';
                    chartOptions.chartArea.left = '5%';
                    chartOptions.chartArea.right = '5%';
                    chartOptions.chartArea.width = '90%';
                    chartOptions.chartArea.height = '90%';

                    chart = new google.visualization.PieChart(element);
                    break;

                case 'table':
                    chartOptions = $.extend({
                        // title: settings.chartOptions.title,
                        legend:'none',
                        sliceVisibilityThreshold:1/50,
                        pieHole:0.5,
                        chartArea : {width:'90%'},
                    }, settings.chartOptions || {});

                    chart = new google.visualization.Table(element);

                    break;

                default:

                    chartOptions = $.extend({
                        // title: settings.chartOptions.title,
                        legend:'none',
                        sliceVisibilityThreshold:1/50,
                        pieHole:0.5,
                        chartArea : {
                            left:50,
                            right:50,
                            top:50,
                            bottom:50
                        },
                    }, settings.chartOptions || {});

                    chart = new google.visualization.ColumnChart(element);
            }

            chart.draw(data, chartOptions);
        };

        this.initChart();

        $(window).resize(function() {
            obj.drawChart();
        });
    };

    // ---------------------------------------------

    $.fn.dchart = function(dataArray, jsonOptions)
    {
        return this.each(function()
        {
            var dchart = new Dchart(this, dataArray, jsonOptions);
            // dchart.drawChart();
        });
    };




})(jQuery);