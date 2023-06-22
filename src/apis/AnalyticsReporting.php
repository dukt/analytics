<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\apis;

use dukt\analytics\base\Api;
use dukt\analytics\models\ReportRequestCriteria;
use dukt\analytics\Plugin;
use Google\Service\AnalyticsData\BatchRunReportsRequest;
use Google\Service\AnalyticsData\BatchRunReportsResponse;
use Google\Service\AnalyticsData\RunReportRequest;
use \Google_Service_AnalyticsReporting;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Report;

class AnalyticsReporting extends Api
{
    // Public Methods
    // =========================================================================

    /**
     * @return Google_Service_AnalyticsReporting
     * @throws \yii\base\InvalidConfigException
     */
    public function getService()
    {
        $client = $this->getClient();

        return new Google_Service_AnalyticsReporting($client);
    }

    /**
     * Get report.
     *
     * @param ReportRequestCriteria $criteria
     * @param bool                  $toArray
     *
     * @return array|Google_Service_AnalyticsReporting_Report
     * @throws \yii\base\InvalidConfigException
     */
    public function getReport(ReportRequestCriteria $criteria, bool $toArray = false)
    {
        $reports = $this->getReports([$criteria], $toArray);

        if (isset($reports[0])) {
            return $reports[0];
        }

        return null;
    }

    /**
     * Get reports.
     *
     * @param array $criterias
     * @param bool  $toArray
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getReports(array $criterias, bool $toArray = false)
    {
        $reportsResponse = $this->getReportingReports($criterias);

        if ($toArray) {
            $reportsResponseArray = (array)$reportsResponse->toSimpleObject();

            return $reportsResponseArray['reports'];
        }

        return $reportsResponse->getReports();
    }

    // Private Methods
    // =========================================================================
    /**
     * Get reporting reports.
     *
     *
     * @return BatchRunReportsResponse
     * @throws \yii\base\InvalidConfigException
     * @param mixed[] $criterias
     */
    private function getReportingReports(array $criterias)
    {
        $requests = [];

        foreach ($criterias as $criteria) {
            $request = $this->getReportingReportRequest($criteria);
            $requests[] = $request;
        }

        $reportsRequest = new BatchRunReportsRequest();
        $reportsRequest->setRequests($requests);

        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();

        return $analyticsData->properties->batchRunReports('properties/309469168', $reportsRequest);
    }

    /**
     * Get reporting report request.
     *
     * @param ReportRequestCriteria $criteria
     *
     * @return \Google\Service\AnalyticsData\RunReportRequest
     * @throws \yii\base\InvalidConfigException
     */
    private function getReportingReportRequest(ReportRequestCriteria $criteria)
    {
        // $request = new RunReportRequest();
        $request = new \Google\Service\AnalyticsData\RunReportRequest();

//        $this->setRequestViewIdFromCriteria($request, $criteria);

        $view = Plugin::getInstance()->getViews()->getViewById($criteria->viewId);
        if ($view !== null) {
            $request->setProperty($view->gaPropertyId);
        }

        $this->setRequestDateRangeFromCriteria($request, $criteria);
        $this->setRequestMetricsFromCriteria($request, $criteria);
        $this->setRequestDimensionsFromCriteria($request, $criteria);

        if ($criteria->samplingLevel) {
            $request->setSamplingLevel($criteria->samplingLevel);
        }

        if (!empty($criteria->orderBys)) {
            $request->setOrderBys($criteria->orderBys);
        }
// Replaced with limit/offset
//        if ($criteria->pageToken) {
//            $pageToken = (string) $criteria->pageToken;
//            $request->setPageToken($pageToken);
//        }
//
//        if ($criteria->pageSize) {
//            $request->setPageSize($criteria->pageSize);
//        }

// Replaced by metric/dimension filter?
//        if ($criteria->filtersExpression) {
//            $request->setFiltersExpression($criteria->filtersExpression);
//        }

        // Replaced by setKeepEmptyRows
//        if ($criteria->includeEmptyRows) {
//            $request->setIncludeEmptyRows($criteria->includeEmptyRows);
//        }

        // Not replaced
//        if ($criteria->hideTotals) {
//            $request->setHideTotals($criteria->hideTotals);
//        }
//
//        if ($criteria->hideValueRanges) {
//            $request->setHideValueRanges($criteria->hideValueRanges);
//        }

        return $request;
    }

    /**
     * @param RunReportRequest $request
     * @param ReportRequestCriteria                           $criteria
     *
     * @throws \yii\base\InvalidConfigException
     */
    private function setRequestViewIdFromCriteria(RunReportRequest &$request, ReportRequestCriteria $criteria)
    {
        if ($criteria->gaViewId) {
            $request->setViewId('ga:'.$criteria->gaViewId);
        } elseif ($criteria->viewId) {
            $view = Plugin::getInstance()->getViews()->getViewById($criteria->viewId);
            if ($view !== null) {
                $request->setViewId($view->gaViewId);
            }
        }
    }

    /**
     * @param RunReportRequest $request
     * @param ReportRequestCriteria $criteria
     */
    private function setRequestDateRangeFromCriteria(RunReportRequest &$request, ReportRequestCriteria $criteria)
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($criteria->startDate);
        $dateRange->setEndDate($criteria->endDate);

        $request->setDateRanges($dateRange);
    }

    /**
     * @param RunReportRequest $request
     * @param ReportRequestCriteria $criteria
     */
    private function setRequestMetricsFromCriteria(RunReportRequest &$request, ReportRequestCriteria $criteria)
    {
        if ($criteria->metrics) {
            $metricString = $criteria->metrics;
//            $metrics = $this->getMetricsFromString($metricString);
            $request->setMetrics(['name' => $metricString]);
        }
    }

    /**
     * @param RunReportRequest $request
     * @param ReportRequestCriteria $criteria
     */
    private function setRequestDimensionsFromCriteria(RunReportRequest &$request, ReportRequestCriteria $criteria)
    {
        if (!empty($criteria->dimensions)) {
            $dimensionString = $criteria->dimensions;
//            $dimensions = $this->getDimensionsFromString($dimensionString);
            $request->setDimensions(['name' => $dimensionString]);
        }
    }

    /**
     * Get dimensions from string.
     *
     * @param $string
     *
     * @return array
     */
    private function getDimensionsFromString($string)
    {
        $dimensions = [];
        $_dimensions = explode(',', $string);
        foreach ($_dimensions as $_dimension) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($_dimension);
            $dimensions[] = $dimension;
        }

        return $dimensions;
    }

    /**
     * Get metrics from string.
     *
     * @param $string
     *
     * @return array
     */
    private function getMetricsFromString($string)
    {
        $metrics = [];
        $_metrics = explode(',', $string);
        foreach ($_metrics as $_metric) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($_metric);
            $metrics[] = $metric;
        }

        return $metrics;
    }
}
