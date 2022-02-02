<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\settings;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;

/**
 * Settings asset bundle.
 */
class SettingsAsset extends AssetBundle
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
            'AccountExplorer.js',
        ];

        $this->css = [
            'AccountExplorer.css',
            'settings.css',
        ];

        parent::init();
    }
}