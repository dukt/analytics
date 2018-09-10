<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\errors\InvalidChartTypeException;
use dukt\analytics\Plugin as Analytics;
use yii\web\Response;

class ReportsController extends Controller
{
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
        } catch(\Google_Service_Exception $e) {
            return $this->handleGoogleServiceException($e);
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
        } catch(\Google_Service_Exception $e) {
            return $this->handleGoogleServiceException($e);
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
        } catch(\Google_Service_Exception $e) {
            return $this->handleGoogleServiceException($e);
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
                        throw new InvalidChartTypeException('Chart type `'.$chart.'` not supported.');
                }

                if ($response) {
                    Analytics::$plugin->cache->set($cacheId, $response);
                }
            }


            return $this->asJson($response);
        } catch(\Google_Service_Exception $e) {
            return $this->handleGoogleServiceException($e);
        }
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

    /**
     * Get realtime demo response.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    private function getRealtimeDemoResponse(): Response
    {
        if (Analytics::$plugin->getAnalytics()->demoMode === 'test') {
            return $this->getRealtimeDemoTestResponse();
        }

        $pageviews = [
            'rows' => []
        ];

        for ($i = 0; $i <= 30; $i++) {
            $pageviews['rows'][] = [$i, random_int(0, 20)];
        }

        $activePages = [
            'rows' => [
                ['/a-new-toga/', random_int(1, 20)],
                ['/parka-with-stripes-on-back/', random_int(1, 20)],
                ['/romper-for-a-red-eye/', random_int(1, 20)],
                ['/the-fleece-awakens/', random_int(1, 20)],
                ['/the-last-knee-high/', random_int(1, 20)],
            ]
        ];

        $activeUsers = 0;

        foreach ($activePages['rows'] as $row) {
            $activeUsers += $row[1];
        }

        return $this->asJson([
            'activeUsers' => $activeUsers,
            'pageviews' => $pageviews,
            'activePages' => $activePages,
        ]);
    }

    /**
     * Get realtime demo test response.
     *
     * @return Response
     * @throws \Exception
     */
    private function getRealtimeDemoTestResponse(): Response
    {
        $pageviews = [
            'rows' => []
        ];

        for ($i = 0; $i <= 30; $i++) {
            $pageviews['rows'][] = [$i, random_int(0, 20)];
        }

        $activePages = [
            'rows' => [
                ['/some-url/', random_int(1, 20)],
                ['/some-super-long-url/with-kebab-case/', random_int(1, 20)],
                ['/somesuperlongurlwithoutkebabcasebutstillsuperlong/', random_int(10000000, 20000000)],
                ['/someothersuperlongurl/withoutkebabcasebutstillsuperlong/', random_int(1, 20)],
                ['/one-last-url/', random_int(1, 20)],
            ]
        ];

        $activeUsers = 0;

        foreach ($activePages['rows'] as $row) {
            $activeUsers += $row[1];
        }

        return $this->asJson([
            'activeUsers' => $activeUsers,
            'pageviews' => $pageviews,
            'activePages' => $activePages,
        ]);
    }

    /**
     * @return Response
     * @throws \Exception
     */
    private function getEcommerceDemoResponse(): Response
    {
        $date = new \DateTime();
        $date = $date->modify('-12 months');
        $rows = [];

        for ($i = 1; $i <= 12; $i++) {
            $rows[] = [
                $date->format('Ym'),
                random_int(50000, 150000)
            ];

            $date->modify('+1 month');
        }

        $reportData = [
            'chart' => [
                'cols' => [
                    [
                        'id' => 'ga:yearMonth',
                        'label' => 'Month of Year',
                        'type' => 'date',
                    ],
                    [
                        'id' => 'ga:transactionRevenue',
                        'label' => 'Revenue',
                        'type' => 'currency',
                    ],
                ],
                'rows' => $rows,
                'totals' => [
                    [
                        '11385.0',
                        '97.3076923076923',
                    ]
                ],
            ],
            'period' => 'year',
            'periodLabel' => 'This year',
            'view' => 'Craft Shop',
        ];

        $totalRevenue = 0;

        foreach($rows as $row) {
            $totalRevenue += $row[1];
        }

        $totalTransactions = random_int(3400, 3800);
        $totalRevenuePerTransaction = $totalRevenue / $totalTransactions;
        $totalTransactionsPerSession = 8.291991495393338;

        return $this->asJson([
            'period' =>  '365daysAgo - today',
            'reportData' => $reportData,
            'totalRevenue' => $totalRevenue,
            'totalRevenuePerTransaction' => $totalRevenuePerTransaction,
            'totalTransactions' => $totalTransactions,
            'totalTransactionsPerSession' => $totalTransactionsPerSession,
        ]);
    }
}
