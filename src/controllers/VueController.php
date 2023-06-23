<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;
use dukt\analytics\Plugin;
use dukt\analytics\Plugin as Analytics;

/**
 * Class VueController
 *
 * @package dukt\analytics\controllers
 */
class VueController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionGetDimensionsMetrics(int $viewId)
    {
        $reportingView = Analytics::$plugin->getViews()->getViewById($viewId);
        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
        $metadata = $analyticsData->properties->getMetadata($reportingView->gaPropertyId.'/metadata');

        $dimensions = array_map(function($dimension) {
            return [
                'apiName' => $dimension->apiName,
                'name' => $dimension->uiName,
            ];
        }, $metadata->getDimensions());

        $metrics = array_map(function($metric) {
            return [
                'apiName' => $metric->apiName,
                'name' => $metric->uiName,
            ];
        }, $metadata->getMetrics());

        return $this->asJson([
            'dimensions' => $dimensions,
            'metrics' => $metrics,
        ]);
    }
}