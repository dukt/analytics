ko.bindingHandlers.myCustomBinding = {
    init: function(element, valueAccessor, allBindings, viewModel, bindingContext) {
        // This will be called when the binding is first applied to an element
        // Set up any initial state, event handlers, etc. here

        viewModel.menuChange();
    }
};

var ViewModel = function() {

    this.selectedView = 'browser';
    this.selectedPeriod = 'week';
    this.selectedChart = ko.observable();

    this.selectedMenu  = ko.observable('location');
    this.selectedMetric  = ko.observable('ga:pageviews');
    this.selectedDimension  = ko.observable();

    this.enabledCharts = ko.observableArray();
    this.dimensions = ko.observableArray();
    this.metrics = ko.observableArray();


    this.menuChange = function()
    {
        console.log('menuChange');

        var value = this.selectedMenu();
        var section = this.getSection(value);

        this.dimensions(section.dimensions);
        this.metrics(section.metrics);

        if(section.enabledCharts)
        {
            this.enabledCharts(section.enabledCharts);
        }
        else
        {
            this.enabledCharts(['table', 'pie']);
        }
    };

    this.getSection = function(menu)
    {
        var section = {
            uri: false,
            view: false,
            dimensions: false,
            metrics: false,
            enabledCharts: false,
            realtime: false,
            chart: false,
        };

        $.each(AnalyticsBrowserData, function(sectionKey, sectionObject)
        {
            if(sectionKey == menu)
            {
                $.each(sectionObject, function(foundSectionKey, foundSectionObject)
                {
                    section[foundSectionKey] = foundSectionObject;
                });

                return false;
            }
        });

        return section;
    };
};

