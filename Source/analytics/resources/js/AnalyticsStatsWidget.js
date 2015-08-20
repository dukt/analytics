/**
 * Stats Widget
 */
Analytics.Stats = Garnish.Base.extend({

    requestData: null,

    init: function(element, options)
    {
        console.log('options', options);
        // elements
        this.$element = $('#'+element);
        this.$title = $('.title', this.$element);
        this.$body = $('.body', this.$element);
        this.$date = $('.date', this.$element);
        this.$spinner = $('.spinner', this.$element);
        this.$settingsBtn = $('.dk-settings-btn', this.$element);


        if(typeof(options['settingsModalTemplate']) != 'undefined')
        {
            this.settingsModalTemplate = options['settingsModalTemplate'];
        }


        // default/cached request and response
        this.chartRequest = options['cachedRequest'];
        this.chartResponse = options['cachedResponse'];

        if(typeof(this.chartRequest) != 'undefined')
        {
            this.requestData = this.chartRequest;
        }

        if(typeof(this.chartResponse) != 'undefined')
        {
            this.parseResponse(this.chartResponse);
        }

        // listeners
        this.addListener(this.$settingsBtn, 'click', 'openSettings');
    },

    periodChange: function(ev)
    {
        if(this.requestData)
        {
            this.requestData.period = $(ev.currentTarget).val();
            this.chartResponse = this.sendRequest(this.requestData);
        }
    },

    openSettings: function(ev)
    {
        if(!this.settingsModal)
        {
            $form = $('<form class="settingsmodal modal fitted"></form>').appendTo(Garnish.$bod);
            $body = $('<div class="body"/>').appendTo($form),
            $footer = $('<div class="footer"/>').appendTo($form),
            $buttons = $('<div class="buttons right"/>').appendTo($footer),
            $cancelBtn = $('<div class="btn">'+Craft.t('Cancel')+'</div>').appendTo($buttons),
            $saveBtn = $('<input type="submit" class="btn submit" value="'+Craft.t('Save')+'" />').appendTo($buttons);

            this.settingsModal = new Garnish.Modal($form, {
                visible: false,
                resizable: false
            });

            this.addListener($cancelBtn, 'click', function() {
                this.settingsModal.hide();
            });

            this.addListener($form, 'submit', $.proxy(function(ev) {

                ev.preventDefault();

                var stringData = $('input, textarea, select', $form).filter(':visible').serializeJSON();

                this.requestData = stringData;


                this.$element.parents('.item').data('colspan', this.requestData.colspan);

                this.chartResponse = this.sendRequest(this.requestData);

                this.settingsModal.hide();

                this.saveState();

                Craft.initUiElements();
                Garnish.$win.trigger('resize');

            }, this));

            $('.body', this.settingsModal.$container).html(this.settingsModalTemplate);

            this.$periodSelect = $('.period select', this.settingsModal.$container);
            this.$chartSelect = $('.chart-select select', this.settingsModal.$container);

            if(this.requestData)
            {
                this.$chartSelect.val(this.requestData.chart);
                this.$chartSelect.trigger('change');

                this.$periodSelect.val(this.requestData.period);
                this.$periodSelect.trigger('change');
            }

            Craft.initUiElements();
        }
        else
        {
            this.settingsModal.show();
        }
    },

    saveState: function()
    {

        var data = {
            id: this.$element.data('id'),
            settings: {
                colspan: this.requestData['colspan'],
                chart: this.requestData['chart'],
                period: this.requestData['period'],
                options: this.requestData['options'],
            }
        };


        Craft.queueActionRequest('analytics/saveWidgetState', data, $.proxy(function(response)
        {
            // state saved
        }, this));
    },

    sendRequest: function(data)
    {
        console.log('sendRequest');

        this.$spinner.removeClass('hidden');

        $('.chart', this.$body).remove();

        Craft.postActionRequest('analytics/reports/getChartReport', data, $.proxy(function(response, textStatus)
        {
            this.parseResponse(response);

        }, this));
    },

    parseResponse: function(response)
    {
        this.$spinner.addClass('hidden');

        $chart = $('<div class="chart"></div>');
        $chart.appendTo(this.$body);

        this.$title.html(response.metric);
        this.$date.html(response.periodLabel);


        this.chart = new Analytics.Chart($chart, response);
    }
});
