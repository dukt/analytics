<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\StringHelper;
use dukt\analytics\errors\InvalidElementException;
use dukt\analytics\models\ReportRequestCriteria;
use dukt\analytics\Plugin;
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
        $source = Analytics::$plugin->getSources()->getSourceById($request['sourceId']);

        $tableId = null;

        if ($source !== null) {
            $tableId = $source->gaPropertyId;
        }

        $metrics = $request['metrics'];
        $dimensions = $request['dimensions'] ?? null;
        $optParams = $request['optParams'];


        $cacheId = ['reports.getRealtimeReport', $tableId, $metrics, $optParams];
        $response = Analytics::$plugin->getCache()->get($cacheId);

        if (!$response) {
            $reportRequest = new RunRealtimeReportRequest();
            $reportRequest->setMetrics(['name' => $metrics]);
            if ($dimensions) {
                $reportRequest->setDimensions(['name' => $dimensions]);
            }

            if (isset($request['limit'])) {
                $reportRequest->setLimit($request['limit']);
            }

            $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
            $response = $analyticsData->properties->runRealtimeReport($tableId, $reportRequest, $optParams);

            $cacheDuration = Analytics::$plugin->getSettings()->realtimeRefreshInterval;
            Analytics::$plugin->getCache()->set($cacheId, $response, $cacheDuration);
        }

        return $response;
    }

    /**
     * Get e-commerce report.
     *
     * @param $sourceId
     * @param $period
     *
     * @return array
     */
    public function getEcommerceReport($sourceId, $period)
    {
        $startDate = '7daysAgo';
        $endDate = 'today';
        $dimensions = 'date';

        switch ($period) {
            case 'week':
                $startDate = '7daysAgo';
                break;
            case 'month':
                $startDate = '30daysAgo';
                break;
            case 'year':
                $startDate = '365daysAgo';
                $dimensions = 'yearMonth';
                break;
        }

        $metrics = [
            ['name' => 'totalRevenue'],
            ['name' => 'averageRevenuePerUser'],
            ['name' => 'transactions'],
            ['name' => 'transactionsPerPurchaser'],
        ];

        $criteria = new ReportRequestCriteria;
        $criteria->sourceId = $sourceId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metrics;
        $criteria->dimensions = $dimensions;
        $criteria->keepEmptyRows = true;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $reportData = $this->parseReportingReport($reportResponse, $criteria);

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'period' => $startDate.' - '.$endDate,
            'totalRevenue' => $reportData['totals'][0]->metricValues[0]->value,
            'totalRevenuePerTransaction' => $reportData['totals'][0]->metricValues[1]->value,
            'totalTransactions' => $reportData['totals'][0]->metricValues[2]->value,
            'totalTransactionsPerSession' => $reportData['totals'][0]->metricValues[3]->value,
            'reportData' => [
                'source' => $source->name,
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

        $siteSource = Analytics::$plugin->getSources()->getSiteSourceBySiteId($siteId);

        $sourceId = null;

        if ($siteSource !== null) {
            $sourceId = $siteSource->sourceId;
        }

        $startDate = date('Y-m-d', strtotime('-1 month'));
        $endDate = date('Y-m-d');
        $dimensions = 'date';
        $metrics = $metric;
        $filters = 'pagePath=='.$uri;

        $request = [
            'sourceId' => $sourceId,
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
            $criteria->sourceId = $sourceId;
            $criteria->startDate = $startDate;
            $criteria->endDate = $endDate;
            $criteria->metrics = $metrics;
            $criteria->dimensions = $dimensions;
            $criteria->filtersExpression = $filters;
            $criteria->keepEmptyRows = true;

            $criteria->orderBys = [
                'dimension' => [
                    'dimensionName' => $dimensions,
                ]
            ];

            $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
            $response = $this->parseReportingReport($reportResponse, $criteria);

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
        $sourceId = ($request['sourceId'] ?? null);
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
        $criteria->sourceId = $sourceId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;
        $criteria->keepEmptyRows = true;

        $criteria->orderBys = [
            'dimension' => [
                'dimensionName' => $dimensionString,
            ]
        ];

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse, $criteria);

        $total = $report['totals'][0];

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'source' => $source->name,
            'type' => 'area',
            'chart' => $report,
            'total' => $total,
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $metricString)),
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
        $sourceId = ($request['sourceId'] ?? null);
        $period = ($request['period'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportRequestCriteria;
        $criteria->sourceId = $sourceId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);

        $report = $this->parseReportingReport($reportResponse, $criteria);

        $total = 0;

        if (!empty($report['totals'][0][0]['value'])) {
            $total = $report['totals'][0][0]['value'];
        }

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'source' => $source->name,
            'type' => 'counter',
            'response' => $report,
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $metricString)),
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
        $sourceId = ($request['sourceId'] ?? null);
        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportRequestCriteria;
        $criteria->sourceId = $sourceId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse, $criteria);

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'source' => $source->name,
            'type' => 'pie',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $metricString)),
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
        $sourceId = ($request['sourceId'] ?? null);

        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);

        $criteria = new ReportRequestCriteria;



        $criteria->sourceId = $sourceId;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse, $criteria);

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'source' => $source->name,
            'type' => 'table',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $metricString)),
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
        $sourceId = ($request['sourceId'] ?? null);
        $period = ($request['period'] ?? null);
        $dimensionString = ($request['options']['dimension'] ?? null);
        $metricString = ($request['options']['metric'] ?? null);

        $originDimension = $dimensionString;

        $criteria = new ReportRequestCriteria;
        $criteria->sourceId = $sourceId;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
        $report = $this->parseReportingReport($reportResponse, $criteria);

        $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

        return [
            'source' => $source->name,
            'type' => 'geo',
            'chart' => $report,
            'dimensionRaw' => $originDimension,
            'dimension' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $originDimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->getMetadataGA4()->getDimMet($sourceId, $metricString)),
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
    private function parseReportingReport(RunReportResponse $report, ReportRequestCriteria $criteria = null): array
    {
        $cols = $this->parseReportingReportCols($report, $criteria);
        $rows = $this->parseReportingReportRows($report, $criteria);

        // TODO: Fix totals
        // $totals = [$report->getRows()[0]->getMetricValues()[0]->getValue()];
        $totals = $report->getTotals();

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
    private function parseReportingReportCols(RunReportResponse $report, ReportRequestCriteria $criteria = null): array
    {
        $cols = [];

        $metadata = Analytics::$plugin->getMetadataGA4()->getMetadataBySourceId($criteria->sourceId);

        foreach($report->getDimensionHeaders() as $dimensionHeader) {
            $metadataDimension = null;
            foreach ($metadata->getDimensions() as $row) {
                if ($row->getApiName() == $dimensionHeader->getName()) {
                    $metadataDimension = $row;
                }
            }

            $col = [
                'type' => $this->getDimensionType($dimensionHeader),
                'label' => Craft::t('analytics', $metadataDimension->getUiName()),
                'id' => $dimensionHeader->getName(),
            ];

            $cols[] = $col;
        }

        foreach($report->getMetricHeaders() as $metricHeader) {
            $metadataMetric = null;
            foreach ($metadata->getMetrics() as $row) {
                if ($row->getApiName() == $metricHeader->getName()) {
                    $metadataMetric = $row;
                }
            }

            $col = [
                'type' => $this->getMetricType($metricHeader),
                'label' => Craft::t('analytics', $metadataMetric->getUiName()),
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
    private function parseReportingReportRows(RunReportResponse $report, ReportRequestCriteria $criteria = null): array
    {
        $rows = [];
        foreach($report->getRows() as $row) {
            $rowValues = [];
            foreach($row->getDimensionValues() as $dimensionValue) {
                $rowValues[] = $dimensionValue->getValue();
            }
            foreach($row->getMetricValues() as $key => $metricValue) {
                $metricHeader = $report->getMetricHeaders()[$key];

                switch($this->getMetricType($metricHeader)) {
                    case 'percent':
                        $rowValues[] = $metricValue->getValue() * 100;
                        break;
                    default:
                        $rowValues[] = $metricValue->getValue();
                }
            }

            $rows[] = $rowValues;
        }

        // Pad missing rows with zeros if criteria’s `keepEmptyRows` is true
        if ($criteria && $criteria->keepEmptyRows) {
            // If dimension is `date`, daily
            if ($criteria->dimensions == 'date') {
                // Loop from start date to end date to fill empty rows
                $startDate = new \DateTime($criteria->startDate);
                $endDate = new \DateTime($criteria->endDate);
                $endDate->modify('+1 day');
                $interval = new \DateInterval('P1D');
                $dateRange = new \DatePeriod($startDate, $interval, $endDate);

                $rowsPaddedWithZeros = [];

                // Loop through date range
                foreach ($dateRange as $date) {
                    // Check if row exists for this date
                    $date = $date->format('Ymd');
                    $found = false;
                    foreach ($rows as $row) {
                        if ($row[0] == $date) {
                            // Date found, add row as is
                            $found = true;
                            $rowsPaddedWithZeros[] = $row;
                        }
                    }

                    // Date not found, add row with zeros
                    if (!$found) {
                        $rowPaddedWithZeros = [$date];
                        for ($i = 1; $i <= count($report->getMetricHeaders()); $i++) {
                            $rowPaddedWithZeros[] = "0";
                        }
                        $rowsPaddedWithZeros[] = $rowPaddedWithZeros;
                    }
                }

                $rows = $rowsPaddedWithZeros;
            }

            // If dimension is `yearMonth`, monthly
            if ($criteria->dimensions == 'yearMonth') {
                // Loop from start date to end date to fill empty rows
                $startDate = new \DateTime($criteria->startDate);
                $endDate = new \DateTime($criteria->endDate);
                $endDate->modify('+1 month');
                $interval = new \DateInterval('P1M');
                $dateRange = new \DatePeriod($startDate, $interval, $endDate);

                $rowsPaddedWithZeros = [];
                foreach ($dateRange as $date) {
                    $date = $date->format('Ym');
                    $found = false;
                    foreach ($rows as $row) {
                        if ($row[0] == $date) {
                            $found = true;
                            $rowsPaddedWithZeros[] = $row;
                        }
                    }
                    if (!$found) {
                        $rowPaddedWithZeros = [$date];
                        for ($i = 1; $i <= count($report->getMetricHeaders()); $i++) {
                            $rowPaddedWithZeros[] = "0";
                        }
                        $rowsPaddedWithZeros[] = $rowPaddedWithZeros;
                    }
                }

                $rows = $rowsPaddedWithZeros;
            }
        }

        return $rows;
    }

    private function getDimensionType($dimensionHeader) {
        switch ($dimensionHeader->getName()) {
            case 'date':
            case 'yearMonth':
                return 'date';
            case 'continent':
            case 'continentId':
                return 'continent';
            default:
                return 'string';
        }
    }

    private function getMetricType($metricHeader) {
        switch($metricHeader->getName()) {
            case 'bounceRate':
            case 'cartToViewRate':
            case 'crashFreeUsersRate':
            case 'engagementRate':
            case 'dauPerMau':
            case 'dauPerWau':
            case 'firstTimePurchaserConversionRate':
            case 'itemListClickThroughRate':
            case 'itemPromotionClickThroughRate':
            case 'organicGoogleSearchClickThroughRate':
            case 'purchaseToViewRate':
            case 'purchaserConversionRate':
            case 'sessionConversionRate':
            case 'userConversionRate':
                return 'percent';
            default:
                switch ($metricHeader->getType()) {
                    case 'TYPE_CURRENCY':
                        return 'currency';
                    case 'TYPE_INTEGER':
                        return 'integer';
                    case 'TYPE_FLOAT':
                        return 'float';
                    case 'TYPE_SECONDS':
                        return 'time';
                    default:
                        return $metricHeader->getType();
                }
        }
    }
}
