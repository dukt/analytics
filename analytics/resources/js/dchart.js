var charts = {};

google.load("visualization", "1", {packages:["corechart", "table", "geochart"]});

(function($){

    var Dchart = function(element, dataArray, jsonOptions)
    {
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
            }

        }, jsonOptions);


        // Public method

        var chart = false;
        var data = false;
        var chartOptions = false;

        this.drawChart = function() {
            chart.draw(data, chartOptions);
        };

        this.initTableChart = function()
        {

            chartOptions = $.extend({
                // title: settings.chartOptions.title,
                legend:'none',
                page: 'enable',
                chartArea : {width:'100%', bottom: '50'},
            }, settings.chartOptions || {});

             // var JSONObject = {
             //      cols: [{id: 'task', label: 'Task', type: 'string'},
             //          {id: 'hours', label: 'Hours per Day', type: 'number'}],
             //      rows: [{c:[{v: 'Work', p: {'style': 'border: 7px solid orange;'}}, {v: 11}]},
             //          {c:[{v: 'Eat'}, {v: 2}]},
             //          {c:[{v: 'Commute'}, {v: 2, f: '2.000'}]}]};

            data = new google.visualization.DataTable(dataArray, 0.5);

            chart = new google.visualization.Table(element);

            this.drawChart();
        }

        this.initChart = function()
        {
            if(settings.chartOptions.chartType == 'table') {
                this.initTableChart();
            } else {
                data = google.visualization.arrayToDataTable(dataArray);

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
                            sliceVisibilityThreshold:1/50,
                            pieHole:0.5,
                            vAxis: {textPosition:'in'},
                        }, settings.chartOptions || {});

                        chart = new google.visualization.ColumnChart(element);

                        break;

                    case 'bubble':

                        chartOptions = $.extend({
                            legend:'none',
                            sliceVisibilityThreshold:1/50,
                            pieHole:0.5,
                            vAxis: {textPosition:'in'},
                        }, settings.chartOptions || {});

                        chart = new google.visualization.BubbleChart(element);

                        break;

                    case 'bar':

                        chartOptions = $.extend({
                            legend:'none',
                            vAxis: {
                                textPosition:'in',
                            },
                        }, settings.chartOptions || {});

                        chart = new google.visualization.BarChart(element);

                        break;

                    case 'donut':

                        chartOptions = $.extend(true, {
                            legend:'none',
                            sliceVisibilityThreshold:1/20,
                            pieHole:0.5,
                        }, settings.chartOptions);

                        // chartOptions.chartArea.top = '5%';
                        // chartOptions.chartArea.bottom = '5%';
                        // chartOptions.chartArea.left = '5%';
                        // chartOptions.chartArea.right = '5%';
                        // chartOptions.chartArea.width = '80%';
                        // chartOptions.chartArea.height = '60%';

                        chart = new google.visualization.PieChart(element);
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
            }
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