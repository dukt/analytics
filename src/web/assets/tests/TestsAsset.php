<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\tests;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\web\assets\reportwidget\ReportWidgetAsset;

/**
 * Realtime report widget asset bundle.
 */
class TestsAsset extends AssetBundle
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
            ReportWidgetAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->css = [
            'tests.css',
        ];

        parent::init();
    }
}