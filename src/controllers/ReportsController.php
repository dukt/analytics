<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\errors\InvalidChartTypeException;
use dukt\analytics\Plugin as Analytics;

class ReportsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Get element report.
     *
     * @return \yii\web\Response
     */
    public function actionElement()
    {
        $elementId = Craft::$app->getRequest()->getRequiredParam('elementId');
        $siteId = (int) Craft::$app->getRequest()->getRequiredParam('siteId');
        $metric = Craft::$app->getRequest()->getRequiredParam('metric');

        try {
            $response = Analytics::$plugin->getReports()->getElementReport($elementId, $siteId, $metric);

            return $this->asJson([
                'type' => 'area',
                'chart' => $response
            ]);
        } catch (\Google_Service_Exception $e) {
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if (isset($errors[0]['message'])) {
                $errorMsg = $errors[0]['message'];
            }

            Craft::info('Couldn’t get element data: '.$errorMsg."\r\n".print_r($errors, true), __METHOD__);

            return $this->asErrorJson($errorMsg);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Craft::info('Couldn’t get element data: '.$errorMsg, __METHOD__);

            return $this->asErrorJson($errorMsg);
        }
    }

    /**
     * Get realtime widget report.
     *
     * @return \yii\web\Response
     */
    public function actionRealtimeWidget()
    {
        $newVisitor = 0;
        $returningVisitor = 0;
        $total = 0;

        if (!Analytics::$plugin->getSettings()->demoMode) {
            try {
                $viewId = Craft::$app->getRequest()->getBodyParam('viewId');

                $request = [
                    'viewId' => $viewId,
                    'metrics' => 'ga:activeVisitors',
                    'optParams' => ['dimensions' => 'ga:visitorType']
                ];

                $response = Analytics::$plugin->getReports()->getRealtimeReport($request);


                // total

                if (!empty($response['totalResults'])) {
                    $total = $response['totalResults'];
                }


                // new & returning visitors

                if (!empty($response['rows'])) {
                    $rows = $response['rows'];

                    if (!empty($rows[0][1])) {
                        switch ($rows[0][0]) {
                            case "RETURNING":
                                $returningVisitor = $rows[0][1];
                                break;

                            case "NEW":
                                $newVisitor = $rows[0][1];
                                break;
                        }
                    }

                    if (!empty($rows[1][1])) {
                        switch ($rows[1][0]) {
                            case "RETURNING":
                                $returningVisitor = $rows[1][1];
                                break;

                            case "NEW":
                                $newVisitor = $rows[1][1];
                                break;
                        }
                    }
                }
            } catch (\Google_Service_Exception $e) {
                $errors = $e->getErrors();
                $errorMsg = $e->getMessage();

                if (isset($errors[0]['message'])) {
                    $errorMsg = $errors[0]['message'];
                }

                Craft::info('Couldn’t get realtime widget data: '.$errorMsg."\r\n".print_r($errors, true), __METHOD__);

                return $this->asErrorJson($errorMsg);
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                Craft::info('Couldn’t get element data: '.$errorMsg, __METHOD__);

                return $this->asErrorJson($errorMsg);
            }

            $realtimeHistoryRequest = [
                'viewId' => $viewId,
                'metrics' => 'rt:pageviews',
                'optParams' => ['dimensions' => 'rt:minutesAgo']
            ];

            $realtimeHistory = Analytics::$plugin->getReports()->getRealtimeReport($realtimeHistoryRequest);

        } else {
            // Demo Mode
            $newVisitor = 5;
            $returningVisitor = 7;
            $total = ($newVisitor + $returningVisitor);
        }

        return $this->asJson([
            'total' => $total,
            'newVisitor' => $newVisitor,
            'returningVisitor' => $returningVisitor,
            'realtimeHistory' => $realtimeHistory
        ]);
    }

    /**
     * Get report widget report.
     *
     * @return \yii\web\Response
     */
    public function actionReportWidget()
    {
        try {
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

            $response = Analytics::$plugin->cache->get($cacheId);

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
                        throw new InvalidChartTypeException("Chart type `".$chart."` not supported.");
                }

                if ($response) {
                    Analytics::$plugin->cache->set($cacheId, $response);
                }
            }

            return $this->asJson($response);
        } catch (\Google_Service_Exception $e) {
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if (isset($errors[0]['message'])) {
                $errorMsg = $errors[0]['message'];
            }

            Craft::info('Couldn’t get report widget data: '.$errorMsg."\r\n".print_r($errors, true), __METHOD__);

            return $this->asErrorJson($errorMsg);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            Craft::info('Couldn’t get element data: '.$errorMsg, __METHOD__);

            return $this->asErrorJson($errorMsg);
        }
    }
}
