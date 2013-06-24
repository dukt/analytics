(function($){
   var Dchart = function(element, dataArray, options)
   {
       var elem = $(element);
       var obj = this;

       // Merge options with defaults
       var settings = $.extend({
           chartType: 'donut',
           title:'Default Title'
       }, options || {});

       // Public method

      $(window).resize(function() {
            obj.drawChart();
      });

       this.drawChart = function()
       {
            var data = google.visualization.arrayToDataTable(dataArray);

            if(settings.chartType == 'donut') {

                var chartOptions = {
                  title: settings.title,
                  legend:'none',
                  sliceVisibilityThreshold:1/50,
                  pieHole:0.5,
                  chartArea : {width:'90%'},
                };

                var chart = new google.visualization.PieChart(element);

            } else {
                var chartOptions = {
                    title: settings.title,
                    legend:{position:'bottom'},
                    chartArea : {width:'90%'},
                    vAxis: {minValue: 4, format: '#'},
                    hAxis: {minValue: 4, format: '#', showTextEvery:5}
                };

                var chart = new google.visualization.LineChart(element);
            }

            chart.draw(data, chartOptions);

            console.log('publicMethod() called!');
       };
   };

   $.fn.dchart = function(dataArray, options)
   {
       return this.each(function()
       {
           var dchart = new Dchart(this, dataArray, options);
           dchart.drawChart();
       });
   };
})(jQuery);