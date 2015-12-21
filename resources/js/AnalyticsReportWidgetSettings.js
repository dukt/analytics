Analytics.ReportWidgetSettings = Garnish.Base.extend({
    init: function(id, settings)
    {
        setTimeout($.proxy(function()
        {
            this.$container = $('#'+id);
            this.$form = this.$container.closest('form');

            this.settings = settings;

            this.$chartTypes = $('.chart-picker ul.chart-types li', this.$form);
            this.$chartSelect = $('.chart-select select', this.$form);

            this.addListener(this.$chartTypes, 'click', $.proxy(function(ev) {

                var $target = $(ev.currentTarget);

                this.$chartTypes.removeClass('active');

                $target.addClass('active');

                this.$chartSelect.val($target.data('chart-type'));
                this.$chartSelect.trigger('change');

            }, this));

            this.$chartTypes.filter('[data-chart-type='+this.$chartSelect.val()+']').trigger('click');
        }, this), 1);
    }
});

