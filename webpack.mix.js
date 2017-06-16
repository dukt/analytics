let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */


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


// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.standaloneSass('src', output); <-- Faster, but isolated from Webpack.
// mix.less(src, output);
// mix.stylus(src, output);
// mix.browserSync('my-site.dev');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
