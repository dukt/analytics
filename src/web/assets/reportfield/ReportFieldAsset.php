<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2019, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\reportfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;

/**
 * Report field asset bundle.
 */
class ReportFieldAsset extends AssetBundle
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
            'js/ReportField.js',
        ];

        $this->css = [
            'css/ReportField.css'
        ];

        parent::init();
    }
}