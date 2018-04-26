/** global: Analytics */
/** global: Garnish */
/** global: google */
/**
 * Visualization
 */
Analytics.Visualization = Garnish.Base.extend({

    options: null,
    afterInitStack: [],

    init: function(options) {
        this.options = options;

        if (Analytics.GoogleVisualizationCalled == false) {
            Analytics.GoogleVisualizationCalled = true;

            google.charts.load('current', {
                packages: ['corechart', 'table'],
                language: Analytics.chartLanguage,
                mapsApiKey: Analytics.mapsApiKey,
                callback: $.proxy(function() {
                    Analytics.GoogleVisualizationReady = true;

                    this.onAfterInit();

                    this.onAfterFirstInit();
                }, this)
            });
        } else {
            this.onAfterInit();
        }
    },

    onAfterFirstInit: function() {
        // call inAfterInits that are waiting for initialization completion

        for (i = 0; i < this.afterInitStack.length; i++) {
            this.afterInitStack[i]();
        }
    },

    onAfterInit: function() {
        if (Analytics.GoogleVisualizationReady) {
            this.options.onAfterInit();
        } else {
            // add it to the stack
            this.afterInitStack.push(this.options.onAfterInit);
        }
    }
});