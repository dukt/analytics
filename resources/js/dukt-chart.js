$(document).ready(function() {
    charts = $('.dukt-chart');

    charts.each(function(k,v) {
        var html = $(v).html();
        console.log('#HTML', html);
        var json = $.parseJSON(html);
        console.log('#JSON', json);

        Craft.postActionRequest('analytics/charts/parse', json, function(response) {
            console.log("XXXX", json);
            response = $.parseJSON(response);
            $(v).dchart(response, json);
            // $(v).html(response);
        });
    });
});