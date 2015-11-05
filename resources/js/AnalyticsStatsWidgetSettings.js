Analytics.StatsWidgetSettings = Garnish.Base.extend({
    init: function(form, settings)
    {
        this.$form = form;
        this.settings = settings;

        this.$chartTypes = $('.chart-picker ul.chart-types li', this.$form);
        this.$chartSelect = $('.chart-select select', this.$form);

        this.addListener(this.$form, 'submit', $.proxy(function(ev) {
            this.onSubmit(ev);
        }, this));

        this.addListener(this.$chartTypes, 'click', $.proxy(function(ev) {

            var $target = $(ev.currentTarget);

            this.$chartTypes.removeClass('active');

            $target.addClass('active');

            this.$chartSelect.val($target.data('chart-type'));
            this.$chartSelect.trigger('change');

        }, this));

        this.$chartTypes.filter('[data-chart-type='+this.$chartSelect.val()+']').trigger('click');
    },

    onSubmit: function(ev)
    {
        if(typeof(this.settings.onSubmit) != 'undefined')
        {
            this.settings.onSubmit(ev);
        }
    }
});
