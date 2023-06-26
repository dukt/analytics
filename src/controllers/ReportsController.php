<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\base\DemoControllerTrait;
use dukt\analytics\errors\InvalidChartTypeException;
use dukt\analytics\Plugin;
use dukt\analytics\Plugin as Analytics;
use yii\base\InvalidConfigException;
use yii\web\Response;

class ReportsController extends Controller
{
    use DemoControllerTrait;

    // Public Methods
    // =========================================================================

    /**
     * E-commerce Report
     *
     * @return null
     * @throws \Google_Service_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEcommerceWidget()
    {
        if (Analytics::getInstance()->getAnalytics()->demoMode) {
            return $this->getEcommerceDemoResponse();
        }

        $viewId = Craft::$app->getRequest()->getBodyParam('viewId');
        $period = Craft::$app->getRequest()->getBodyParam('period');

        try {
            $response = Analytics::$plugin->getReports()->getEcommerceReport($viewId, $period);
        } catch(\Google_Service_Exception $googleServiceException) {
            return $this->handleGoogleServiceException($googleServiceException);
        }

        return $this->asJson($response);
    }

    /**
     * Get element report.
     *
     * @return Response
     * @throws \Google_Service_Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionElement()
    {
        $elementId = Craft::$app->getRequest()->getRequiredParam('elementId');
        $siteId = (int)Craft::$app->getRequest()->getRequiredParam('siteId');
        $metric = Craft::$app->getRequest()->getRequiredParam('metric');

        try {
            $response = Analytics::$plugin->getReports()->getElementReport($elementId, $siteId, $metric);
        } catch(\Google_Service_Exception $googleServiceException) {
            return $this->handleGoogleServiceException($googleServiceException);
        }

        return $this->asJson([
            'type' => 'area',
            'chart' => $response
        ]);
    }

    /**
     * Get realtime widget report.
     *
     * @return Response
     * @throws \Google_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRealtimeWidget()
    {
        if (Analytics::getInstance()->getAnalytics()->demoMode) {
            return $this->getRealtimeDemoResponse();
        }


        // Active users

        $activeUsers = 0;

        $viewId = Craft::$app->getRequest()->getBodyParam('viewId');

        $request = [
            'viewId' => $viewId,
            'metrics' => 'ga:activeVisitors',
            'optParams' => []
        ];

        try {
            $response = Analytics::$plugin->getReports()->getRealtimeReport($request);
        } catch(\Google_Service_Exception $googleServiceException) {
            return $this->handleGoogleServiceException($googleServiceException);
        }

        if (!empty($response['totalsForAllResults']) && isset($response['totalsForAllResults']['ga:activeVisitors'])) {
            $activeUsers = $response['totalsForAllResults']['ga:activeVisitors'];
        }


        // Pageviews

        $pageviewsRequest = [
            'viewId' => $viewId,
            'metrics' => 'rt:pageviews',
            'optParams' => ['dimensions' => 'rt:minutesAgo']
        ];

        $pageviews = Analytics::$plugin->getReports()->getRealtimeReport($pageviewsRequest);


        // Active pages

        $activePagesRequest = [
            'viewId' => $viewId,
            'metrics' => 'rt:activeUsers',
            'optParams' => ['dimensions' => 'rt:pagePath', 'max-results' => 5]
        ];

        $activePages = Analytics::$plugin->getReports()->getRealtimeReport($activePagesRequest);

        return $this->asJson([
            'activeUsers' => $activeUsers,
            'pageviews' => $pageviews,
            'activePages' => $activePages,
        ]);
    }

    /**
     * Get report widget report.
     *
     * @return Response
     * @throws InvalidChartTypeException
     * @throws \Google_Service_Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionReportWidget()
    {
        $viewId = Craft::$app->getRequest()->getBodyParam('viewId');
        $chart = Craft::$app->getRequest()->getBodyParam('chart');
        $period = Craft::$app->getRequest()->getBodyParam('period');
        $options = Craft::$app->getRequest()->getBodyParam('options');

        $request = [
            'viewId' => $viewId,
            'chart' => $chart,
            'period' => $period,
            'options' => $options,
        ];

        $cacheId = ['getReport', $request];

        try {
            $response = Analytics::$plugin->getCache()->get($cacheId);

            if (!$response) {
                switch ($chart) {
                    case 'area':
                        $response = Analytics::$plugin->getReports()->getAreaReport($request);
                        break;
                    case 'counter':
                        $response = Analytics::$plugin->getReports()->getCounterReport($request);
                        break;
                    case 'pie':
                        $response = Analytics::$plugin->getReports()->getPieReport($request);
                        break;
                    case 'table':
                        $response = Analytics::$plugin->getReports()->getTableReport($request);
                        break;
                    case 'geo':
                        $response = Analytics::$plugin->getReports()->getGeoReport($request);
                        break;
                    default:
                        throw new InvalidChartTypeException('Chart type `'.$chart.'` not supported.');
                }

                if ($response) {
                    Analytics::$plugin->getCache()->set($cacheId, $response);
                }
            }


            return $this->asJson($response);
        } catch(\Google_Service_Exception $googleServiceException) {
            return $this->handleGoogleServiceException($googleServiceException);
        }
    }

    /**
     * Get dimensions and metrics
     *
     * @param int $viewId
     * @return Response
     * @throws InvalidConfigException
     */
    public function actionGetDimensionsMetrics(int $viewId)
    {
        $reportingView = Analytics::$plugin->getViews()->getViewById($viewId);
        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
        $metadata = $analyticsData->properties->getMetadata($reportingView->gaPropertyId.'/metadata');

        $dimensions = array_map(function($dimension) {
            return [
                'apiName' => $dimension->apiName,
                'name' => $dimension->uiName,
                'category' => $dimension->category,
            ];
        }, $metadata->getDimensions());

        $metrics = array_map(function($metric) {
            return [
                'apiName' => $metric->apiName,
                'name' => $metric->uiName,
                'category' => $metric->category,
            ];
        }, $metadata->getMetrics());

        return $this->asJson([
            'dimensions' => $dimensions,
            'metrics' => $metrics,
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * Handle Google service exception.
     *
     * @param \Google_Service_Exception $e
     * @return Response
     * @throws \Google_Service_Exception
     */
    private function handleGoogleServiceException(\Google_Service_Exception $e): Response
    {
        $errors = $e->getErrors();

        if(empty($errors)) {
            throw $e;
        }

        Craft::error("Couldn’t generate Report widget’s report: \r\n".print_r($errors, true)."\r\n".$e->getTraceAsString(), __METHOD__);

        return $this->asErrorJson($errors[0]['message']);
    }
}
