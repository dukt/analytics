<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\analyticsvue;

use Craft;
use craft\helpers\ChartHelper;
use craft\helpers\Json;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;
use craft\web\View;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\googlecharts\GoogleChartsAsset;

/**
 * Vue asset bundle.
 */
class AnalyticsVueAsset extends AssetBundle
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
            GoogleChartsAsset::class,
            VueAsset::class,
        ];
        
        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $mapsApiKey = Craft::parseEnv(Analytics::$plugin->getSettings()->mapsApiKey);
        $continents = Analytics::$plugin->getGeo()->getContinents();
        $subContinents = Analytics::$plugin->getGeo()->getSubContinents();
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
        $js .= 'Analytics.d3FormatLocaleDefinition = window.d3FormatLocaleDefinition;';
        $js .= '}';

        $view->registerJs($js, View::POS_BEGIN);
    }
}