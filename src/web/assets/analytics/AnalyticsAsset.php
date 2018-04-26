<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\web\assets\analytics;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\helpers\ChartHelper;
use craft\web\View;
use craft\helpers\Json;
use dukt\analytics\Plugin as Analytics;

class AnalyticsAsset extends AssetBundle
{
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
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'Analytics.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $mapsApiKey = Analytics::$plugin->getSettings()->mapsApiKey;
        $continents = Analytics::$plugin->metadata->getContinents();
        $subContinents = Analytics::$plugin->metadata->getSubContinents();
        $formats = ChartHelper::formats();

        $js = "if(typeof Analytics == 'undefined') {";

        $js .= 'var Analytics = {};';
        $js .= 'Analytics.mapsApiKey = "'.$mapsApiKey.'";';
        $js .= 'Analytics.GoogleVisualizationCalled = false;';
        $js .= 'Analytics.GoogleVisualizationReady = false;';
        $js .= 'Analytics.reports = {};';
        $js .= 'Analytics.continents = '.Json::encode($continents).';';
        $js .= 'Analytics.subContinents = '.Json::encode($subContinents).';';
        $js .= 'Analytics.formats = '.Json::encode($formats).';';
        $js .= 'Analytics.chartLanguage = "'.Analytics::$plugin->getAnalytics()->getChartLanguage().'";';

        $js .= '}';

        $view->registerJs($js, View::POS_BEGIN);
    }
}