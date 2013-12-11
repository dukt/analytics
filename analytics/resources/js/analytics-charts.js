$(document).ready(function() {

    // bar, donut, bubble, column, line

    charts = $('.analyticsChart.bar, .analyticsChart.donut, .analyticsChart.bubble, .analyticsChart.column, .analyticsChart.line');

    charts.each(function(k,v) {
        var html = $('.data', v).html();

        var json = $.parseJSON(html);

        setTimeout(function() {
            Craft.postActionRequest('analytics/charts/parse', json, function(response) {
                $(v).dchart(response, json);
            });
        }, 500);
    });


    // table

    charts = $('.analyticsChart.table');

    charts.each(function(k,v) {
        var options = $('.data', v).html();

        var json = $.parseJSON(options);

        setTimeout(function() {
            Craft.postActionRequest('analytics/charts/parseTable', json, function(response) {
                $(v).dchart(response, json);
            });
        }, 500);
    });
});