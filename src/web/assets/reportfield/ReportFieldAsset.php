<?php
namespace dukt\analytics\web\assets\reportfield;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ReportFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@dukt/analytics/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
            \dukt\analytics\web\assets\analytics\AnalyticsAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/ReportField.js',
            'js/jsapi.js',
        ];

        $this->css = [
            'css/ReportField.css'
        ];

        parent::init();
    }
}