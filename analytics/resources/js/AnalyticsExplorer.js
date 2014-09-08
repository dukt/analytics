
AnalyticsExplorer = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $('#'+element);
        this.$currentNav = 'audience';


        // nav
        this.$navBtn = $('.analytics-nav:first', this.$element);
        this.$nav = this.$navBtn.menubtn().data('menubtn').menu;
        this.$nav.on('optionselect', $.proxy(this, 'onNavChange'));

        // browser
        this.$browser = new AnalyticsBrowser(element);

        // realtime
        this.$realtime = new AnalyticsRealtimeReport(element);
    },

    onNavChange: function(ev)
    {
        // option
        this.$nav.$options.removeClass('sel');
        var $option = $(ev.selectedOption).addClass('sel');
        this.$navBtn.html($option.html());

        // value
        $value = $option.data('item');

        // display view
        $view = AnalyticsBrowserSections[$value].view;
        $('.analytics-view', this.$element).addClass('hidden');
        $('.analytics-view[data-view="'+$view+'"]', this.$element).removeClass('hidden');

        // update current nav
        this.$currentNav = $value;

        if($view == 'browser')
        {
            this.$realtime.stop();
            this.$browser.changeCurrentNav($value);
        }
        else if($view == 'realtime')
        {
            this.$realtime.start();
        }
    }
});

