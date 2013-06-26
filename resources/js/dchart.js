var charts = {};

google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(function() {
      // initialize all dcharts once ready
});

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
              title:'Default Title'
            }

       }, jsonOptions);

       // Public method

       this.drawChart = function()
       {
            var data = google.visualization.arrayToDataTable(dataArray);



            if(settings.chartOptions.chartType == 'line') {

                var chartOptions = $.extend({
                    title: settings.chartOptions.title,
                    legend:{position:'bottom'},
                    chartArea : {width:'90%'},
                    vAxis: {minValue: 4, format: '#'},
                    hAxis: {minValue: 4, format: '#', showTextEvery:5}
                 }, settings.chartOptions || {});

            } else {

                var chartOptions = $.extend({
                      title: settings.chartOptions.title,
                      legend:'none',
                      sliceVisibilityThreshold:1/50,
                      pieHole:0.5,
                      chartArea : {width:'90%'},
               }, settings.chartOptions || {});
            }


            if(settings.chartOptions.chartType == 'column') {
              var chart = new google.visualization.ColumnChart(element);
            } else if(settings.chartOptions.chartType == 'donut') {
              var chart = new google.visualization.PieChart(element);
            } else {
              var chart = new google.visualization.LineChart(element);
            }


            chart.draw(data, chartOptions);
       };

      $(window).resize(function() {
            obj.drawChart();
      });
   };

   $.fn.dchart = function(dataArray, jsonOptions)
   {
       return this.each(function()
       {
           var dchart = new Dchart(this, dataArray, jsonOptions);
           dchart.drawChart();
       });
   };
})(jQuery);