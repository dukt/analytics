$(document).ready(function() {

    Craft.postActionRequest('analytics/plugin/checkUpdates', function(response) {
        console.log('checkUpdates');

        $('.dukt-update .plugin-name').text(response.class)
    });
});