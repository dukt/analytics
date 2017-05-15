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
        $dateRanges = Plugin::$plugin->getApi4()->getAnalyticsReportingDateRange($startDate, $endDate);
        $metrics = Plugin::$plugin->getApi4()->getMetricsFromString($_metrics);
        $dimensions = Plugin::$plugin->getApi4()->getDimensionsFromString($_dimensions);
        $request = Plugin::$plugin->getApi4()->getAnalyticsReportingReportRequest($viewId, $dateRanges, $metrics, $dimensions);

        $requests = Plugin::$plugin->getApi4()->getAnalyticsReportingGetReportsRequest(array($request));
        $response = Plugin::$plugin->getApi4()->getAnalyticsReporting()->reports->batchGet($requests);
        $reports = Plugin::$plugin->getApi4()->parseResponse($response);

        Craft::$app->getUrlManager()->setRouteParams([
            'response' => $response,
            'reports' => $reports,
        ]);
    }
}