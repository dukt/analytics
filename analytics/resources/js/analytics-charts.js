$(document).ready(function() {

    // bar, donut, bubble, column, line

    charts = $('.analyticsChart.bar, .analyticsChart.donut, .analyticsChart.bubble, .analyticsChart.column, .analyticsChart.line');

    charts.each(function(k,v) {

        var html = $('.data', v).html();

        var json = $.parseJSON(html);


        Craft.postActionRequest('analytics/charts/parse', json, function(response) {
            if(typeof(response.error) != 'undefined') {
                $('.error .inject', v).html(response.error.message);
                $('.error', v).removeClass('hidden');

                $(v).parents('.analyticsTab').find('.more').addClass('hidden');


                $(v).addClass('error');
            } else {
                $(v).dchart(response, json);
            }

            $(v).addClass('dk-loaded');
        });

    });


    // table

    charts = $('.analyticsChart.table');

    charts.each(function(k,v) {
        var options = $('.data', v).html();

        var json = $.parseJSON(options);

        Craft.postActionRequest('analytics/charts/parseTable', json, function(response) {
            if(typeof(response.error) != 'undefined') {
                $('.error .inject', v).html(response.error.message);
                $('.error', v).removeClass('hidden');

                $(v).addClass('error');
            } else {
                $(v).dchart(response, json);
            }

            $(v).addClass('dk-loaded');
        });

    });
});