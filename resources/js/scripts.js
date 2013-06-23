$(document).ready(function() {
    $('.analytics-setting .change').click(function() {
        var parent = $(this).parents('.analytics-setting');

        $('.analytics-setting').addClass('closed');

        $(parent).removeClass('closed');
        $(parent).removeClass('done');

        return false;
    });
});