<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\errors\InvalidElementException;
use dukt\analytics\models\ReportRequestCriteria;
use dukt\analytics\Plugin;
use Google\Service\AnalyticsData\BatchRunReportsRequest;
use Google\Service\AnalyticsData\RunRealtimeReportRequest;
use Google\Service\AnalyticsData\RunRealtimeReportResponse;
use yii\base\Component;
use dukt\analytics\Plugin as Analytics;
use Google\Service\AnalyticsData\RunReportResponse;

class Reports extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a realtime report.
     *
     * @param array $request
     *
     * @return RunRealtimeReportResponse
     * @throws \yii\base\InvalidConfigException
     */
    public function getRealtimeReport(array $request)
    {
        $view = Analytics::$plugin->getViews()->getViewById($request['viewId']);

        $tableId = null;

        if ($view !== null) {
            $tableId = $view->gaPropertyId;
        }

        $metrics = $request['metrics'];
        $dimensions = $request['dimensions'] ?? null;
        $optParams = $request['optParams'];


        $cacheId = ['reports.getRealtimeReport', $tableId, $metrics, $optParams];
        $response = Analytics::$plugin->getCache()->get($cacheId);

        if (!$response) {
//            $reportsRequest = new BatchRunReportsRequest();
//            $reportsRequest->setRequests($requests);
//
//            $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
//
//            return $analyticsData->properties->batchRunReports('properties/309469168', $reportsRequest);
//
            $reportRequest = new RunRealtimeReportRequest();
            $reportRequest->setMetrics(['name' => $metrics]);
            if ($dimensions) {
                $reportRequest->setDimensions(['name' => $dimensions]);
            }

            if (isset($request['limit'])) {
                $reportRequest->setLimit($request['limit']);
            }

            $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
//            var_dump($tableId);
//            die();
            $response = $analyticsData->properties->runRealtimeReport($tableId, $reportRequest, $optParams);

//            $response = Analytics::$plugin->getApis()->getAnalytics()->getService()->data_realtime->get($tableId, $metrics, $optParams);


            $cacheDuration = Analytics::$plugin->getSettings()->realtimeRefreshInterval;
            Analytics::$plugin->getCache()->set($cacheId, $response, $cacheDuration);
        }

        return $response;
    }

    /**
     * Get e-commerce report.
     *
     * @param $viewId
     * @param $period
     *
     * @return array
     */
    public function getEcommerceReport($viewId, $period)
    {
        $startDate = '7daysAgo';
        $endDate = 'today';
        $dimensions = 'ga:date';

        switch ($period) {
            case 'week':
                $startDate = '7daysAgo';
                break;
            case 'month':
                $startDate = '30daysAgo';
                break;
            case 'year':
                $startDate = '365daysAgo';
                $dimensions = 'ga:yearMonth';
                break;
        }

        $metrics = 'ga:transactionRevenue,ga:revenuePerTransaction,ga:transactions,ga:transactionsPerSession';

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metrics;
        $criteria->dimensions = $dimensions;
        $criteria->includeEmptyRows = true;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $reportResponse->toSimpleObject();
        $reportData = $this->parseReportingReport($reportResponse);

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'period' => $startDate.' - '.$endDate,
            'totalRevenue' => $report->data->totals[0]->values[0],
            'totalRevenuePerTransaction' => $report->data->totals[0]->values[1],
            'totalTransactions' => $report->data->totals[0]->values[2],
            'totalTransactionsPerSession' => $report->data->totals[0]->values[3],
            'reportData' => [
                'view' => $view->name,
                'chart' => $reportData,
                'period' => $period,
                'periodLabel' => Craft::t('analytics', 'This '.$period)
            ],
        ];
    }

    /**
     * Returns an element report.
     *
     *
     * @return array
     * @throws \Exception
     */
    public function getElementReport(int $elementId, ?int $siteId, string $metric)
    {
        $uri = Analytics::$plugin->getAnalytics()->getElementUrlPath($elementId, $siteId);

        if (!$uri) {
            throw new InvalidElementException("Element doesn't support URLs.", 1);
        }

        if ($uri === '__home__') {
            $uri = '';
        }

        $siteView = Analytics::$plugin->getViews()->getSiteViewBySiteId($siteId);

        $viewId = null;

        if ($siteView !== null) {
            $viewId = $siteView->viewId;
        }

        $startDate = date('Y-m-d', strtotime('-1 month'));
        $endDate = date('Y-m-d');
        $dimensions = 'date';
        $metrics = $metric;
        $filters = 'pagePath=='.$uri;

        $request = [
            'viewId' => $viewId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
            'filters' => $filters
        ];

        $cacheId = ['reports.getElementReport', $request];
        $response = Analytics::$plugin->getCache()->get($cacheId);

        if (!$response) {

            $criteria = new ReportRequestCriteria;
            $criteria->viewId = $viewId;
            $criteria->startDate = $startDate;
            $criteria->endDate = $endDate;
            $criteria->metrics = $metrics;
            $criteria->dimensions = $dimensions;
            $criteria->filtersExpression = $filters;
            $criteria->includeEmptyRows = true;

            $criteria->orderBys = [
                'dimension' => [
                    'dimensionName' => $dimensions,
                ]
            ];

            $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
            $response = $this->parseReportingReport($reportResponse);

            if ($response) {
                Analytics::$plugin->getCache()->set($cacheId, $response);
            }
        }

        return $response;
    }

    /**
     * Returns an area report.
     *
     * @param array $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getAreaReport(array $request)
    {
        $viewId = ($request['viewId'] ?? null);
        $period = ($request['period'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);

        if ($period == 'year') {
            $dimensionString = 'yearMonth';
            $startDate = date('Y-m-01', strtotime('-1 '.$period));
            $endDate = date('Y-m-d');
        } else {
            $dimensionString = 'date';
            $startDate = date('Y-m-d', strtotime('-1 '.$period));
            $endDate = date('Y-m-d');
        }

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;
        $criteria->includeEmptyRows = true;

        $criteria->orderBys = [
            'dimension' => [
                'dimensionName' => $dimensionString,
            ]
        ];

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse);

        $total = $report['totals'][0];

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'area',
            'chart' => $report,
            'total' => $total,
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'This '.$period)
        ];
    }

    /**
     * Returns a counter report.
     *
     * @param array $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getCounterReport(array $request)
    {
        $viewId = ($request['viewId'] ?? null);
        $period = ($request['period'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse);

        $total = 0;

        if (!empty($report['totals'][0])) {
            $total = $report['totals'][0];
        }

        $counter = [
            'type' => $report['cols'][0]['type'],
            'value' => $total,
            // 'label' => StringHelper::toLowerCase(Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)))
            'label' => $metricString
        ];

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'counter',
            'counter' => $counter,
            'response' => $report,
//            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)),
            'metric' => $metricString,
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a pie report.
     *
     * @param array $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getPieReport(array $request)
    {
        $viewId = ($request['viewId'] ?? null);
        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse);

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'pie',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a table report.
     *
     * @param array $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getTableReport(array $request)
    {
        $viewId = ($request['viewId'] ?? null);

        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);

        $criteria = new ReportRequestCriteria;



        $criteria->viewId = $viewId;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse);

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'table',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'This '.$period)
        ];
    }

    /**
     * Returns a geo report.
     *
     * @param array $request
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getGeoReport(array $request)
    {
        $viewId = ($request['viewId'] ?? null);
        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);

        $originDimension = $dimensionString;

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse);

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'geo',
            'chart' => $report,
            'dimensionRaw' => $originDimension,
//            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($originDimension)),
            'dimension' => $originDimension,
//            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($metricString)),
            'metric' => $metricString,
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'This '.$period)
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * @param RunReportResponse $report
     * @return array
     */
    private function parseReportingReport(RunReportResponse $report): array
    {
        $cols = $this->parseReportingReportCols($report);
        $rows = $this->parseReportingReportRows($report);

        // TODO: Fix totals
        // $totals = [$report->getRows()[0]->getMetricValues()[0]->getValue()];
        $totals = [0];

        return [
            'cols' => $cols,
            'rows' => $rows,
            'totals' => $totals
        ];
    }

    /**
     * @param RunReportResponse $report
     * @return array
     */
    private function parseReportingReportCols(RunReportResponse $report): array
    {
        $cols = [];

        foreach($report->getDimensionHeaders() as $dimensionHeader) {
            $type = '';

            switch ($dimensionHeader->getName()) {
                case 'date':
                case 'yearMonth':
                    $type = 'date';
                    break;

                case 'continent':
                case 'continentId':
                    $type = 'continent';
                    break;

                default:
                    $type = 'string';
            }

            $col = [
                'type' => $type,
                'label' => Craft::t('analytics', $dimensionHeader->getName()),
                'id' => $dimensionHeader->getName(),
            ];

            $cols[] = $col;
        }

        foreach($report->getMetricHeaders() as $metricHeader) {
            $col = [
                'type' => $metricHeader->getType() === 'TYPE_INTEGER' ? 'integer' : $metricHeader->getType(),
                'label' => Craft::t('analytics', $metricHeader->getName()),
                'id' => $metricHeader->getName(),
            ];

            $cols[] = $col;
        }

        return $cols;
    }

    /**
     * @param RunReportResponse $report
     * @return array
     */
    private function parseReportingReportRows(RunReportResponse $report): array
    {
        $rows = [];
        foreach($report->getRows() as $row) {
            $rowValues = [];
            foreach($row->getDimensionValues() as $dimensionValue) {
                $rowValues[] = $dimensionValue->getValue();
            }
            foreach($row->getMetricValues() as $metricValue) {
                $rowValues[] = $metricValue->getValue();
            }
            $rows[] = $rowValues;

        }
        return $rows;
    }
}
