<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\StringHelper;
use dukt\analytics\errors\InvalidElementException;
use dukt\analytics\models\ReportRequestCriteria;
use yii\base\Component;
use dukt\analytics\Plugin as Analytics;
use \Google_Service_AnalyticsReporting_Report;

class Reports extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a realtime report.
     *
     * @param array $request
     *
     * @return array
     */
    public function getRealtimeReport(array $request)
    {
        $view = Analytics::$plugin->getViews()->getViewById($request['viewId']);

        $tableId = null;

        if ($view) {
            $tableId = 'ga:'.$view->gaViewId;
        }

        $metrics = $request['metrics'];
        $optParams = $request['optParams'];

        $cacheId = ['reports.getRealtimeReport', $tableId, $metrics, $optParams];
        $response = Analytics::$plugin->cache->get($cacheId);

        if (!$response) {
            $response = Analytics::$plugin->getApis()->getAnalytics()->getService()->data_realtime->get($tableId, $metrics, $optParams);

            $cacheDuration = Analytics::$plugin->getSettings()->realtimeRefreshInterval;
            Analytics::$plugin->cache->set($cacheId, $response, $cacheDuration);
        }

        return (array)$response;
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

        switch ($period) {
            case 'week':
                $startDate = '7daysAgo';
                break;
            case 'month':
                $startDate = '30daysAgo';
                break;
            case 'year':
                $startDate = '365daysAgo';
                break;
        }

        $dimensions = 'ga:date';
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

        return [
            'period' => $startDate.' - '.$endDate,
            'totalRevenue' => $report->data['totals'][0]['values'][0],
            'totalRevenuePerTransaction' => $report->data['totals'][0]['values'][1],
            'totalTransactions' => $report->data['totals'][0]['values'][2],
            'totalTransactionsPerSession' => $report->data['totals'][0]['values'][3],
            'reportData' => [
                'chart' => $reportData
            ],
        ];
    }

    /**
     * Returns an element report.
     *
     * @param int      $elementId
     * @param int|null $siteId
     * @param string   $metric
     *
     * @return array
     * @throws \Exception
     */
    public function getElementReport($elementId, $siteId, $metric)
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

        if ($siteView) {
            $viewId = $siteView->viewId;
        }

        $startDate = date('Y-m-d', strtotime('-1 month'));
        $endDate = date('Y-m-d');
        $dimensions = 'ga:date';
        $metrics = $metric;
        $filters = "ga:pagePath==".$uri;

        $request = [
            'viewId' => $viewId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
            'filters' => $filters
        ];

        $cacheId = ['reports.getElementReport', $request];
        $response = Analytics::$plugin->cache->get($cacheId);

        if (!$response) {

            $criteria = new ReportRequestCriteria;
            $criteria->viewId = $viewId;
            $criteria->startDate = $startDate;
            $criteria->endDate = $endDate;
            $criteria->metrics = $metrics;
            $criteria->dimensions = $dimensions;
            $criteria->filtersExpression = $filters;

            $reportResponse = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($criteria);
            $response = $this->parseReportingReport($reportResponse);

            if ($response) {
                Analytics::$plugin->cache->set($cacheId, $response);
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
     */
    public function getAreaReport(array $request)
    {
        $viewId = (isset($request['viewId']) ? $request['viewId'] : null);
        $period = (isset($request['period']) ? $request['period'] : null);
        $metricString = (isset($request['options']['metric']) ? $request['options']['metric'] : null);

        switch ($period) {
            case 'year':
                $dimensionString = 'ga:yearMonth';
                $startDate = date('Y-m-01', strtotime('-1 '.$period));
                $endDate = date('Y-m-d');
                break;

            default:
                $dimensionString = 'ga:date';
                $startDate = date('Y-m-d', strtotime('-1 '.$period));
                $endDate = date('Y-m-d');
        }

        $criteria = new ReportRequestCriteria;
        $criteria->viewId = $viewId;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;
        $criteria->orderBys = [
            ["fieldName" => $dimensionString, "orderType" => 'VALUE', "sortOrder" => 'ASCENDING']
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
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
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
     */
    public function getCounterReport(array $request)
    {
        $viewId = (isset($request['viewId']) ? $request['viewId'] : null);
        $period = (isset($request['period']) ? $request['period'] : null);
        $metricString = (isset($request['options']['metric']) ? $request['options']['metric'] : null);
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
            'label' => StringHelper::toLowerCase(Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)))
        ];

        $view = Analytics::$plugin->getViews()->getViewById($viewId);

        return [
            'view' => $view->name,
            'type' => 'counter',
            'counter' => $counter,
            'response' => $report,
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
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
     */
    public function getPieReport(array $request)
    {
        $viewId = (isset($request['viewId']) ? $request['viewId'] : null);
        $period = (isset($request['period']) ? $request['period'] : null);
        $dimensionString = (isset($request['options']['dimension']) ? $request['options']['dimension'] : null);
        $metricString = (isset($request['options']['metric']) ? $request['options']['metric'] : null);
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
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
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
     */
    public function getTableReport(array $request)
    {
        $viewId = (isset($request['viewId']) ? $request['viewId'] : null);

        $period = (isset($request['period']) ? $request['period'] : null);
        $dimensionString = (isset($request['options']['dimension']) ? $request['options']['dimension'] : null);
        $metricString = (isset($request['options']['metric']) ? $request['options']['metric'] : null);

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
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a geo report.
     *
     * @param array $request
     *
     * @return array
     */
    public function getGeoReport(array $request)
    {
        $viewId = (isset($request['viewId']) ? $request['viewId'] : null);
        $period = (isset($request['period']) ? $request['period'] : null);
        $dimensionString = (isset($request['options']['dimension']) ? $request['options']['dimension'] : null);
        $metricString = (isset($request['options']['metric']) ? $request['options']['metric'] : null);

        $originDimension = $dimensionString;

        if ($dimensionString === 'ga:city') {
            $dimensionString = 'ga:latitude,ga:longitude,'.$dimensionString;
        }

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
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($originDimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Google_Service_AnalyticsReporting_Report $_report
     *
     * @return array
     */
    private function parseReportingReport(Google_Service_AnalyticsReporting_Report $_report)
    {
        $columnHeader = $_report->getColumnHeader();
        $columnHeaderDimensions = $columnHeader->getDimensions();
        $metricHeaderEntries = $columnHeader->getMetricHeader()->getMetricHeaderEntries();


        // Columns

        $cols = [];

        if ($columnHeaderDimensions) {
            foreach ($columnHeaderDimensions as $columnHeaderDimension) {

                $id = $columnHeaderDimension;
                $label = Analytics::$plugin->metadata->getDimMet($columnHeaderDimension);

                switch ($columnHeaderDimension) {
                    case 'ga:date':
                    case 'ga:yearMonth':
                        $type = 'date';
                        break;

                    case 'ga:continent':
                        $type = 'continent';
                        break;
                    case 'ga:subContinent':
                        $type = 'subContinent';
                        break;

                    case 'ga:latitude':
                    case 'ga:longitude':
                        $type = 'float';
                        break;

                    default:
                        $type = 'string';
                }

                $col = [
                    'type' => $type,
                    'label' => Craft::t('analytics', $label),
                    'id' => $id,
                ];

                array_push($cols, $col);
            }
        }

        foreach ($metricHeaderEntries as $metricHeaderEntry) {
            $label = Analytics::$plugin->metadata->getDimMet($metricHeaderEntry['name']);

            $col = [
                'type' => strtolower($metricHeaderEntry['type']),
                'label' => Craft::t('analytics', $label),
                'id' => $metricHeaderEntry['name'],
            ];

            array_push($cols, $col);
        }


        // Rows

        $rows = [];

        foreach ($_report->getData()->getRows() as $_row) {

            $colIndex = 0;
            $row = [];

            $dimensions = $_row->getDimensions();

            if ($dimensions) {
                foreach ($dimensions as $_dimension) {

                    $value = $_dimension;

                    if ($columnHeaderDimensions) {
                        if (isset($columnHeaderDimensions[$colIndex])) {
                            switch ($columnHeaderDimensions[$colIndex]) {
                                case 'ga:continent':
                                    $value = Analytics::$plugin->metadata->getContinentCode($value);
                                    break;
                                case 'ga:subContinent':
                                    $value = Analytics::$plugin->metadata->getSubContinentCode($value);
                                    break;
                            }
                        }
                    }

                    array_push($row, $value);

                    $colIndex++;
                }
            }

            foreach ($_row->getMetrics() as $_metric) {
                array_push($row, $_metric->getValues()[0]);
                $colIndex++;
            }

            array_push($rows, $row);
        }

        $totals = $_report->getData()->getTotals()[0]->getValues();

        return [
            'cols' => $cols,
            'rows' => $rows,
            'totals' => $totals
        ];
    }
}
