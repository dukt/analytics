$(document).ready(function() {
    charts = $('.dukt-chart');

    charts.each(function(k,v) {
        var html = $('.data', v).html();

        var json = $.parseJSON(html);

        console.log(json);

        Craft.postActionRequest('analytics/charts/parse', json, function(response) {

            response = $.parseJSON(response);

            $(v).dchart(response, json);

        });
    });
});