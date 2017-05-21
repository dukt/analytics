<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\Plugin;

class Api4Controller extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex($response=null, array $reports=null)
    {
        $variables['startDate'] = Craft::$app->getRequest()->getParam('startDate');
        $variables['endDate'] = Craft::$app->getRequest()->getParam('endDate');
        $variables['metrics'] = Craft::$app->getRequest()->getParam('metrics');
        $variables['dimensions'] = Craft::$app->getRequest()->getParam('dimensions');

        $variables['response'] = $response;
        $variables['reports'] = $reports;

        $this->renderTemplate('analytics/api4/_index', $variables);
    }

    public function actionGetReport()
    {
        $startDate = Craft::$app->getRequest()->getParam('startDate');
        $endDate = Craft::$app->getRequest()->getParam('endDate');
        $_metrics = Craft::$app->getRequest()->getParam('metrics');
        $_dimensions = Craft::$app->getRequest()->getParam('dimensions');

        $viewId = Plugin::$plugin->getAnalytics()->getProfileId();
        $dateRanges = Plugin::$plugin->getApi()->getAnalyticsReportingDateRange($startDate, $endDate);
        $metrics = Plugin::$plugin->getApi()->getMetricsFromString($_metrics);
        $dimensions = Plugin::$plugin->getApi()->getDimensionsFromString($_dimensions);
        $request = Plugin::$plugin->getApi()->getAnalyticsReportingReportRequest($viewId, $dateRanges, $metrics, $dimensions);

        $requests = Plugin::$plugin->getApi()->getAnalyticsReportingGetReportsRequest(array($request));
        $response = Plugin::$plugin->getApi()->getAnalyticsReporting()->reports->batchGet($requests);
        $reports = Plugin::$plugin->getApi()->parseReportsResponseApiV4($response);

        Craft::$app->getUrlManager()->setRouteParams([
            'response' => $response,
            'reports' => $reports,
        ]);
    }
}
