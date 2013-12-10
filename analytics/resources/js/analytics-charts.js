$(document).ready(function() {

    charts = $('.analyticsChart.bar, .analyticsChart.donut, .analyticsChart.bubble, .analyticsChart.column, .analyticsChart.line');

    charts.each(function(k,v) {
        var html = $('.data', v).html();

        var json = $.parseJSON(html);

        Craft.postActionRequest('analytics/charts/parse', json, function(response) {

            response = $.parseJSON(response);

            $(v).dchart(response, json);

        });
    });


    charts = $('.analyticsChart.table');

    charts.each(function(k,v) {
        var options = $('.data', v).html();

        var json = $.parseJSON(options);

        Craft.postActionRequest('analytics/charts/parseTable', json, function(response) {

            response = $.parseJSON(response);

            $(v).dchart(response, json);

        });
    });
});