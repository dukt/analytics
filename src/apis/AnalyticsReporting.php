<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\apis;

use dukt\analytics\base\Api;
use dukt\analytics\models\ReportRequestCriteria;
use dukt\analytics\Plugin as Analytics;
use \Google_Service_AnalyticsReporting;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_GetReportsRequest;
use \Google_Service_AnalyticsReporting_GetReportsResponse;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Report;
use \Google_Service_AnalyticsReporting_ReportRequest;

class AnalyticsReporting extends Api
{
    // Public Methods
    // =========================================================================

    /**
     * @return Google_Service_AnalyticsReporting
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
     * @param array $criterias
     *
     * @return Google_Service_AnalyticsReporting_GetReportsResponse
     */
    private function getReportingReports($criterias)
    {
        $requests = [];

        foreach ($criterias as $criteria) {
            $request = $this->getReportingReportRequest($criteria);
            array_push($requests, $request);
        }

        $reportsRequest = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $reportsRequest->setReportRequests($requests);

        return $this->getService()->reports->batchGet($reportsRequest);
    }

    /**
     * Get reporting report request.
     *
     * @param ReportRequestCriteria $criteria
     *
     * @return Google_Service_AnalyticsReporting_ReportRequest
     */
    private function getReportingReportRequest(ReportRequestCriteria $criteria)
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();

        if($criteria->gaViewId) {
            $request->setViewId('ga:'.$criteria->gaViewId);
        } else {
            if ($criteria->viewId) {
                $view = Analytics::$plugin->getViews()->getViewById($criteria->viewId);

                if ($view) {
                    $request->setViewId($view->gaViewId);
                }
            }
        }

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($criteria->startDate);
        $dateRange->setEndDate($criteria->endDate);
        $request->setDateRanges($dateRange);

        if ($criteria->samplingLevel) {
            $request->setSamplingLevel($criteria->samplingLevel);
        }

        if ($criteria->metrics) {
            $metricString = $criteria->metrics;
            $metrics = $this->getMetricsFromString($metricString);
            $request->setMetrics($metrics);
        }

        if ($criteria->dimensions) {
            $dimensionString = $criteria->dimensions;
            $dimensions = $this->getDimensionsFromString($dimensionString);
            $request->setDimensions($dimensions);
        }

        if ($criteria->orderBys) {
            $request->setOrderBys($criteria->orderBys);
        }

        if ($criteria->pageToken) {
            $pageToken = $criteria->pageToken;

            if(is_int($pageToken)) {
                $pageToken = (string) $pageToken;
            }

            $request->setPageToken($pageToken);
        }

        if ($criteria->pageSize) {
            $request->setPageSize($criteria->pageSize);
        }

        if ($criteria->filtersExpression) {
            $request->setFiltersExpression($criteria->filtersExpression);
        }

        if($criteria->includeEmptyRows) {
            $request->setIncludeEmptyRows($criteria->includeEmptyRows);
        }

        if($criteria->hideTotals) {
            $request->setHideTotals($criteria->hideTotals);
        }

        if($criteria->hideValueRanges) {
            $request->setHideValueRanges($criteria->hideValueRanges);
        }

        return $request;
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
        $_dimensions = explode(",", $string);
        foreach ($_dimensions as $_dimension) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($_dimension);
            array_push($dimensions, $dimension);
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
        $_metrics = explode(",", $string);
        foreach ($_metrics as $_metric) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($_metric);
            array_push($metrics, $metric);
        }

        return $metrics;
    }
}
