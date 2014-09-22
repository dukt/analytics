
AnalyticsExplorer = Garnish.Base.extend({
    init: function(element)
    {
        this.$element = $('#'+element);
        this.$currentMenu = 'mobile';


        // nav
        this.$menu = $('.analytics-menu:first select:first', this.$element);
        this.$menu.on('change', $.proxy(this, 'onMenuChange'));

        // browser
        this.$browser = new AnalyticsBrowser(element);

        // realtime
        //this.$realtime = new AnalyticsRealtimeReport(element);
    },

    onMenuChange: function(ev)
    {
        $value = $(ev.currentTarget).val();
        this.$currentMenu = $value;

        // view
        console.log('value', $value);
        $view = AnalyticsBrowserData[$value].view;
        $('.analytics-view', this.$element).addClass('hidden');
        $('.analytics-view[data-view="'+$view+'"]', this.$element).removeClass('hidden');

        // update current nav
        this.$currentNav = $value;

        if($view == 'browser')
        {
            // this.$realtime.stop();
            this.$browser.changeCurrentNav($value);
        }
        else if($view == 'realtime')
        {
            // this.$realtime.start();
        }
    }
});

