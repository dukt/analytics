/** global: Analytics */
/** global: Garnish */
/**
 * Chart Options
 */
Analytics.ChartOptions = Garnish.Base.extend({}, {
    area: function(scale) {
        var options = JSON.parse(JSON.stringify(this.defaults.area));

        switch (scale) {
            case 'week':
                options.hAxis.format = 'E';
                break;

            case 'month':
                options.hAxis.format = 'MMM d';
                break;

            case 'year':
                options.hAxis.format = 'MMM yy';
                break;
        }

        return options;
    },

    table: function() {
        return this.defaults.table;
    },

    geo: function(dimension) {
        var options = this.defaults.geo;

        switch (dimension) {
            case 'ga:city':
                options.displayMode = 'markers';
                break;

            case 'ga:country':
                options.resolution = 'countries';
                options.displayMode = 'regions';
                break;

            case 'ga:continent':
                options.resolution = 'continents';
                options.displayMode = 'regions';
                break;

            case 'ga:subContinent':
                options.resolution = 'subcontinents';
                options.displayMode = 'regions';
                break;
        }

        return options;
    },

    pie: function() {
        return this.defaults.pie;
    },

    field: function() {
        return {
            theme: 'maximized',
            legend: 'none',
            backgroundColor: '#fdfdfd',
            colors: ['#058DC7'],
            areaOpacity: 0.1,
            lineWidth: 4,
            height: 120,
            hAxis: {
                format: 'MMM d',
                textPosition: 'in',
                textStyle: {
                    color: '#058DC7'
                },
                showTextEvery: 1,
                baselineColor: '#fff',
                gridlines: {
                    color: 'none'
                }
            },
            vAxis: {
                textPosition: 'in',
                textStyle: {
                    color: '#058DC7'
                },
                baselineColor: '#ccc',
                gridlines: {
                    color: '#f4f4f4'
                },
                // viewWindow: {min:0, max: 10},
                minValue: 0,
                maxValue: 10,
                format: '#'
            }
        };
    },

    defaults: {
        area: {
            theme: 'maximized',
            legend: 'none',
            backgroundColor: 'transparent',
            colors: ['#058DC7'],
            areaOpacity: 0.1,
            pointSize: 7,
            lineWidth: 4,
            chartArea: {},
            hAxis: {
                textPosition: 'in',
                textStyle: {
                    color: '#058DC7'
                },
                baselineColor: '#fff',
                gridlines: {
                    color: 'none'
                }
            },
            vAxis: {
                textPosition: 'in',
                textStyle: {
                    color: '#058DC7'
                },
                baselineColor: '#ccc',
                gridlines: {
                    color: '#fafafa'
                },
                // viewWindow: {min:0, max: 10},
                minValue: 0,
                maxValue: 10,
                format: '#'
            }
        },

        geo: {
            // height: 282
            displayMode: 'auto'
        },

        pie: {
            theme: 'maximized',
            height: 282,
            pieHole: 0.5,
            legend: {
                alignment: 'center',
                position: 'top'
            },
            chartArea: {
                top: 40,
                height: '82%'
            },
            sliceVisibilityThreshold: 1 / 120
        },

        table: {
            page: 'auto',
            pageSize: 10,
        }
    }
});

/** global: Analytics */

Analytics.Metadata = {
    getContinentByCode: function(code) {
        var continent;

        $.each(Analytics.continents, function(key, _continent) {
            if (code == _continent.code) {
                continent = _continent.label;
            }
        });

        if (continent) {
            return continent;
        }

        return code;
    },

    getSubContinentByCode: function(code) {
        var continent;

        $.each(Analytics.subContinents, function(key, _continent) {
            if (code == _continent.code) {
                continent = _continent.label;
            }
        });

        if (continent) {
            return continent;
        }

        return code;
    }
};

/** global: Analytics */
/** global: google */
/**
 * Utils
 */
Analytics.Utils = {
    responseToDataTable: function(response, localeDefinition) {
        var dataTable = new google.visualization.DataTable();


        // Columns

        $.each(response.cols, function(key, column) {
            var dataTableColumnType;

            switch (column.type) {
                case 'date':
                    dataTableColumnType = 'date';
                    break;
                case 'percent':
                case 'time':
                case 'integer':
                case 'currency':
                case 'float':
                    dataTableColumnType = 'number';
                    break;

                default:
                    dataTableColumnType = 'string';
            }

            dataTable.addColumn({
                type: dataTableColumnType,
                label: column.label,
                id: column.id,
            });
        });


        // Rows

        $.each(response.rows, $.proxy(function(keyRow, row) {

            var dataTableRow = [];

            $.each(response.cols, $.proxy(function(keyColumn, column) {
                switch (column.type) {
                    case 'date':
                        dataTableRow[keyColumn] = Analytics.Utils.formatByType(localeDefinition, column.type, row[keyColumn]);
                        break;

                    case 'float':
                        dataTableRow[keyColumn] = +row[keyColumn];
                        break;

                    case 'integer':
                    case 'currency':
                    case 'percent':
                    case 'time':
                    case 'continent':
                    case 'subContinent':
                        dataTableRow[keyColumn] = {
                            v: Analytics.Utils.formatRawValueByType(localeDefinition, column.type, row[keyColumn]),
                            f: Analytics.Utils.formatByType(localeDefinition, column.type, row[keyColumn])
                        };
                        break;

                    default:
                        dataTableRow[keyColumn] = row[keyColumn];
                }
            }, this));

            dataTable.addRow(dataTableRow);

        }, this));

        return dataTable;
    },

    formatRawValueByType: function(localeDefinition, type, value) {
        switch (type) {
            case 'integer':
            case 'currency':
            case 'percent':
            case 'time':
            case 'float':
                return +value;
                break;

            default:
                return value;
        }
    },

    formatByType: function(localeDefinition, type, value) {
        switch (type) {
            case 'continent':
                return Analytics.Metadata.getContinentByCode(value);
                break;

            case 'subContinent':
                return Analytics.Metadata.getSubContinentByCode(value);
                break;

            case 'currency':
                return Analytics.Utils.formatCurrency(localeDefinition, +value);
                break;

            case 'float':
                return +value;
                break;

            case 'integer':
                return Analytics.Utils.formatInteger(localeDefinition, +value);
                break;

            case 'time':
                return Analytics.Utils.formatDuration(+value);
                break;

            case 'percent':
                return Analytics.Utils.formatPercent(localeDefinition, +value);
                break;

            case 'date':
                $dateString = value;

                if ($dateString.length == 8) {
                    // 20150101

                    $year = eval($dateString.substr(0, 4));
                    $month = eval($dateString.substr(4, 2)) - 1;
                    $day = eval($dateString.substr(6, 2));

                    $date = new Date($year, $month, $day);

                    return $date;
                } else if ($dateString.length == 6) {
                    // 201501

                    $year = eval($dateString.substr(0, 4));
                    $month = eval($dateString.substr(4, 2)) - 1;

                    $date = new Date($year, $month, '01');

                    return $date;
                }
                break;

            default:
                return value;
                break;
        }
    },

    formatCurrency: function(localeDefinition, value) {
        var d3Locale = this.getD3Locale(localeDefinition);
        var formatter = d3Locale.format(Craft.charts.BaseChart.defaults.formats.currencyFormat);

        return formatter(value);
    },

    formatDuration: function(_seconds) {
        var sec_num = parseInt(_seconds, 10); // don't forget the second param
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);

        if (hours < 10) {
            hours = "0" + hours;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        return hours + ':' + minutes + ':' + seconds;
    },

    formatInteger: function(localeDefinition, value) {
        return this.getD3Locale(localeDefinition).format(",")(value);
    },

    formatPercent: function(localeDefinition, value) {
        return this.getD3Locale(localeDefinition).format(Craft.charts.BaseChart.defaults.formats.percentFormat)(value / 100);
    },

    getD3Locale: function(localeDefinition) {
        return d3.formatLocale(localeDefinition);
    },

    _getD3LocaleDefinition(extendLocaleDefinition) {
        return $.extend(true, Analytics.d3FormatLocaleDefinition, extendLocaleDefinition);
    },

    getLocaleDefinition(currencyDefinition) {
        // Get D3 locale definition with currency definition set from the widget's settings
        return this._getD3LocaleDefinition({
            currency: currencyDefinition
        })
    }
};

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
                packages: ['corechart', 'table', 'geochart'],
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
/** global: Analytics */
/** global: google */
/**
 * BaseChart
 */
Analytics.reports.BaseChart = Garnish.Base.extend(
    {
        $chart: null,
        $graph: null,

        localeDefinition: null,

        type: null,
        chart: null,
        chartOptions: null,
        data: null,
        period: null,
        options: null,
        visualization: null,
        drawing: false,

        init: function($element, data, localeDefinition, chartLanguage) {
            this.visualization = new Analytics.Visualization(
                {
                    chartLanguage: chartLanguage,
                    onAfterInit: $.proxy(function() {
                        this.$chart = $element;
                        this.$chart.html('');
                        this.$graph = $('<div class="chart" />').appendTo(this.$chart);
                        this.data = data;
                        this.localeDefinition = localeDefinition;

                        if (typeof(this.data.chartOptions) != 'undefined') {
                            this.chartOptions = this.data.chartOptions;
                        }

                        if (typeof(this.data.type) != 'undefined') {
                            this.type = this.data.type;
                        }

                        if (typeof(this.data.period) != 'undefined') {
                            this.period = this.data.period;
                        }

                        this.addListener(Garnish.$win, 'resize', 'resize');

                        this.initChart();

                        this.draw();

                        if (typeof(this.data.onAfterInit) != 'undefined') {
                            this.data.onAfterInit();
                        }

                    }, this)
                });
        },

        addChartReadyListener: function() {
            google.visualization.events.addListener(this.chart, 'ready', $.proxy(function() {
                this.drawing = false;

                if (typeof(this.data.onAfterDraw) != 'undefined') {
                    this.data.onAfterDraw();
                }

            }, this));
        },

        initChart: function() {
            this.$graph.addClass(this.type);
        },

        draw: function() {
            if (!this.drawing) {
                this.drawing = true;

                if (this.dataTable && this.chartOptions) {

                    this.chart.draw(this.dataTable, this.chartOptions);
                }
            }
        },

        resize: function() {
            if (this.chart && this.dataTable && this.chartOptions) {
                this.draw(this.dataTable, this.chartOptions);
            }
        },
    });
/** global: Analytics */
/** global: google */
/**
 * Area
 */
Analytics.reports.Area = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.metric);
            $period.html(this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);

            this.chartOptions = Analytics.ChartOptions.area(this.data.period);

            if (typeof(this.data.chartOptions) != 'undefined') {
                $.extend(this.chartOptions, this.data.chartOptions);
            }


            // generate ticks

            var ticks = [];

            for (i = 0; i < this.data.chart.rows.length; i++) {
                var rowDate = this.data.chart.rows[i][0]
                var tickYear = rowDate.substr(0, 4)
                var tickMonth = rowDate.substr(4, 2) - 1
                var tickDay = rowDate.substr(6, 2)
                var tickDate = new Date(tickYear, tickMonth, tickDay)
                ticks.push(tickDate)
            }

            this.chartOptions.hAxis.ticks = ticks


            // period

            if (this.data.period == 'year') {
                var dateFormatter = new google.visualization.DateFormat({
                    pattern: "MMMM yyyy"
                });

                dateFormatter.format(this.dataTable, 0);
            }

            this.chart = new google.visualization.AreaChart(this.$graph.get(0));

            this.addChartReadyListener();
        }
    });

/** global: Analytics */
/**
 * Counter
 */
Analytics.reports.Counter = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            $value = $('<div class="value" />').appendTo(this.$graph),
                $label = $('<div class="label" />').appendTo(this.$graph),
                $period = $('<div class="period" />').appendTo(this.$graph);
            $view = $('<div class="view" />').appendTo(this.$graph);

            var value = Analytics.Utils.formatByType(this.localeDefinition, this.data.counter.type, this.data.counter.value);

            $value.html(value);
            $label.html(this.data.metric);
            $period.html(' ' + this.data.periodLabel);
            $view.html(' ' + this.data.view);

            this.data.onAfterDraw();
        }
    });
/** global: Analytics */
/** global: google */
/**
 * Geo
 */
Analytics.reports.Geo = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.metric);
            $period.html(this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);
            this.chartOptions = Analytics.ChartOptions.geo(this.data.dimensionRaw);
            this.chart = new google.visualization.GeoChart(this.$graph.get(0));

            this.addChartReadyListener();
        }
    });

/** global: Analytics */
/** global: google */
/**
 * Pie
 */
Analytics.reports.Pie = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.dimension);
            $period.html(this.data.metric + " " + this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);
            this.chartOptions = Analytics.ChartOptions.pie();
            this.chart = new google.visualization.PieChart(this.$graph.get(0));

            this.chartOptions.height = this.$graph.height();

            this.addChartReadyListener();
        }
    });

/** global: Analytics */
/** global: google */
/**
 * Table
 */
Analytics.reports.Table = Analytics.reports.BaseChart.extend(
    {
        initChart: function() {
            this.base();

            var $reportTitle = $('<div class="subtitle" />').prependTo(this.$chart),
                $period = $('<div class="period" />').appendTo($reportTitle),
                $view = $('<div class="view" />').appendTo($reportTitle),
                $title = $('<div class="title" />').prependTo(this.$chart);

            $view.html(this.data.view);
            $title.html(this.data.metric);
            $period.html(this.data.periodLabel);

            this.dataTable = Analytics.Utils.responseToDataTable(this.data.chart, this.localeDefinition);
            this.chartOptions = Analytics.ChartOptions.table();
            this.chart = new google.visualization.Table(this.$graph.get(0));

            this.addChartReadyListener();
        },

        resize: function() {
            // disable resize for the table chart
        },
    });