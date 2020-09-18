<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2020, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\realtimereportwidget;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;

/**
 * Realtime report widget asset bundle.
 */
class RealtimeReportWidgetAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = __DIR__.'/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            AnalyticsAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'RealtimeWidget.js',
        ];

        $this->css = [
            'RealtimeWidget.css',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $js = "if(typeof AnalyticsRealtime == 'undefined') {";

        $js .= 'var AnalyticsRealtime = {};';
        $js .= 'AnalyticsRealtime.GoogleVisualizationCalled = false;';
        $js .= 'AnalyticsRealtime.GoogleVisualizationReady = false;';

        $js .= '}';

        $view->registerJs($js, View::POS_BEGIN);
    }
}