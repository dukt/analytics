$(document).ready(function() {

});

Analytics.ChartWidgetSettings = Garnish.Base.extend({
    init: function(container)
    {
        this.$container = container;

        // $('#main #content form', this.$container).submit(function(ev) {
        //     var $hiddenElements = $('input[type=text], textarea, select:not([name=chart])', this).filter(':hidden');
        //     console.log('$hiddenElements', $hiddenElements);
        //     $hiddenElements.remove();
        // });

        $('.chart-picker ul.chart-types li', this.$container).click(function() {
            $('.chart-picker ul.chart-types li', this.$container).removeClass('active');
            $(this).addClass('active');
            $('.chart-select select', this.$container).val($(this).data('chart-type'));
            $('.chart-select select', this.$container).trigger('change');
        });
    }
});
