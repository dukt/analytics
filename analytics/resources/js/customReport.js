google.load("visualization", "1", {packages:['corechart', 'table', 'geochart']});

// google.setOnLoadCallback(drawChart);

$('.analytics-trigger-load').click(function() {

    console.log('analytics-trigger-load');

    $id = $(this).data('widget-id');

    console.log('widget-id', $id);

    Craft.postActionRequest('analytics/customReport', {id: $id}, function(response) {

        var chartData = new google.visualization.DataTable();

        $.each(response.apiResponse.columnHeaders, function(k, columnHeader) {
            console.log('Column H', k, columnHeader);

            $type = 'string';

            if(columnHeader.name == 'ga:date') {
                $type = 'date';
            }
            else
            {
                if(columnHeader.dataType == 'INTEGER')
                {
                    $type = 'number';
                }
            }

            chartData.addColumn($type, columnHeader.name);
            console.log('ch', $type, columnHeader.name);
        });

        $.each(response.apiResponse.rows, function(k, row) {
            // response.apiResponse.rows[k][0] = eval(response.apiResponse.rows[k][0]);


            $.each(response.apiResponse.columnHeaders, function(k2, columnHeader) {

                if(columnHeader.name == 'ga:date') {

                        $date = response.apiResponse.rows[k][k2];
                        $year = $date.substr(0, 4);
                        $month = $date.substr(4, 2);
                        $day = $date.substr(6, 2);
                        response.apiResponse.rows[k][k2] = new Date($year, $month, $day);

                }
                else
                {
                    if(columnHeader.dataType == 'INTEGER')
                    {
                        response.apiResponse.rows[k][k2] = eval(response.apiResponse.rows[k][k2]);
                    }
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