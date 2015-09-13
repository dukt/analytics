$(document).ready(function() {

});

Analytics.ChartWidgetSettings = Garnish.Base.extend({
    init: function(container)
    {
        this.$container = container;

        $('#main #content form', this.$container).submit(function(ev) {
            $('input[type=text], textarea, select', this).filter(':hidden').remove();
        });

        $('.chart-picker ul.chart-types li', this.$container).click(function() {
            $('.chart-picker ul.chart-types li', this.$container).removeClass('active');
            $(this).addClass('active');
            $('.chart-select select', this.$container).val($(this).data('chart-type'));
            $('.chart-select select', this.$container).trigger('change');
        });
    }
});
