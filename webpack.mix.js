let mix = require('laravel-mix');


// Combine Analytics.js

mix.combine([
    'src/web/assets/analytics/src/Analytics/*.js',
    'src/web/assets/analytics/src/Analytics/reports/Base.js',
    'src/web/assets/analytics/src/Analytics/reports/Area.js',
    'src/web/assets/analytics/src/Analytics/reports/Counter.js',
    'src/web/assets/analytics/src/Analytics/reports/Geo.js',
    'src/web/assets/analytics/src/Analytics/reports/Pie.js',
    'src/web/assets/analytics/src/Analytics/reports/Table.js',
], 'src/web/assets/analytics/dist/Analytics.js');


// Minify JS

mix.minify('src/web/assets/analytics/dist/Analytics.js');
mix.minify('src/web/assets/realtimereportwidget/dist/RealtimeWidget.js');
mix.minify('src/web/assets/reportfield/dist/js/ReportField.js');
mix.minify('src/web/assets/reportwidget/dist/js/ReportWidget.js');
mix.minify('src/web/assets/reportwidget/dist/js/ReportWidgetSettings.js');
mix.minify('src/web/assets/settings/dist/AccountExplorer.js');


// Compile SASS

mix
    .sass('src/web/assets/realtimereportwidget/dist/RealtimeWidget.scss', 'src/web/assets/realtimereportwidget/dist')
    .sass('src/web/assets/reportfield/dist/css/ReportField.scss', 'src/web/assets/reportfield/dist/css')
    .sass('src/web/assets/reportwidget/dist/css/ReportWidget.scss', 'src/web/assets/reportwidget/dist/css')
    .sass('src/web/assets/reportwidget/dist/css/ReportWidgetSettings.scss', 'src/web/assets/reportwidget/dist/css')
    .sass('src/web/assets/settings/dist/AccountExplorer.scss', 'src/web/assets/settings/dist')
    .sass('src/web/assets/settings/dist/settings.scss', 'src/web/assets/settings/dist')
    .sass('src/web/assets/tests/dist/tests.scss', 'src/web/assets/tests/dist')
    .options({
        processCssUrls: false
    });
