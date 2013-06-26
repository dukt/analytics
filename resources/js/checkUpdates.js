$(document).ready(function() {

    Craft.postActionRequest('analytics/plugin/checkUpdates', function(response) {
        console.log('checkUpdates');

        if(response) {
            $('.dukt-update .plugin-name').text(response.class);
            $('.dukt-update').css('display', 'block');
            $('.dukt-update').animate({
                opacity: 1,
              }, 500, function() {
                // Animation complete.
              });
        }
    });
});