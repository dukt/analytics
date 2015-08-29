$(document).ready(function() {
    $('#main #content form').submit(function(ev) {
        $('input[type=text], textarea, select', this).filter(':hidden').remove();
    });
});
