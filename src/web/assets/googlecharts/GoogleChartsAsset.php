<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\web\assets\googlecharts;

use craft\web\AssetBundle;
use craft\web\View;

class GoogleChartsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        $view->registerJsFile('//www.gstatic.com/charts/loader.js', [
            'position' => View::POS_HEAD,
        ]);
    }
}