google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

// google.setOnLoadCallback(drawChart);

$('.analytics-trigger-load').click(function() {

    console.log('analytics-trigger-load');

    $id = $(this).data('widget-id');

    console.log('widget-id', $id);

    Craft.postActionRequest('analytics/customReport', {id: $id}, function(response) {

        var chartData = new google.visualization.DataTable();

        $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {
            //console.log('Column H', k, columnHeader);

            $type = 'string';

            if(columnHeader.dataType == 'INTEGER')
            {
                $type = 'number';
            }

            chartData.addColumn($type, columnHeader.name);
            console.log('ch', $type, columnHeader.name);
        });

        $.each(response.apiResponse.rows, function(k, row) {
            // response.apiResponse.rows[k][0] = eval(response.apiResponse.rows[k][0]);


            $.each(response.apiResponse.columnHeaders, function(k2, columnHeader) {
                if(columnHeader.dataType == 'INTEGER')
                {
                    response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                }
            });
        });

        console.log(response.apiResponse.rows);

        chartData.addRows(response.apiResponse.rows);


        var options = {
          title: 'Company Performance',
          vAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
        };

        console.log('widget', response.widget.settings.options.chartType);

        var chart = false;

        switch(response.widget.settings.options.chartType)
        {
            case "AreaChart":
            chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
            break;

            case "BarChart":
            chart = new google.visualization.BarChart(document.getElementById('chart_div'));
            break;

            case "ColumnChart":
            chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            break;

            case "PieChart":
            chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            break;
        }

        if(chart)
        {
            chart.draw(chartData, options);
        }


        // totals

        // $('#chart_totals')

        $.each(response.apiResponse.totalsForAllResults, function(k, v) {
            console.log(k, v);

            $li = $('<li><strong>'+k+':</strong> '+v+'</li>')
            $('#chart_totals').append($li);
        });
        //console.log("xxxx", response.apiResponse.totalsForAllResults['ga:visits']);
    });

    return false;
});

$(document).ready(function() {
    $('.analytics-trigger-load').trigger('click');
});

// function drawChart() {
//     var data = google.visualization.arrayToDataTable([
//       ['Year', 'Sales'],
//       ['2004',  1000],
//       ['2005',  1170],
//       ['2006',  660],
//       ['2007',  1030]
//     ]);

//     // response.columnHeaders
//     // response.rows

//     // // Declare columns
//     // data.addColumn('string', 'Employee Name');
//     // data.addColumn('DateTime', 'Hire Date');

//     // // Add data.
//     // data.addRows([
//     //   ['Mike', {v:new Date(2008,1,28), f:'February 28, 2008'}], // Example of specifying actual and formatted values.
//     //   ['Bob', new Date(2007,5,1)],                              // More typically this would be done using a
//     //   ['Alice', new Date(2006,7,16)],                           // formatter.
//     //   ['Frank', new Date(2007,11,28)],
//     //   ['Floyd', new Date(2005,3,13)],
//     //   ['Fritz', new Date(2011,6,1)]
//     // ]);

//     var options = {
//       title: 'Company Performance',
//       vAxis: {title: 'Year',  titleTextStyle: {color: 'red'}}
//     };

//     var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
//     chart.draw(data, options);
// }