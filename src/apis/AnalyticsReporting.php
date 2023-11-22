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
use Google\Service\AnalyticsData\FilterExpression;
use Google\Service\AnalyticsData\RunReportRequest;
use \Google_Service_AnalyticsReporting;
use \Google_Service_AnalyticsReporting_DateRange;

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
     * @return \Google\Service\AnalyticsData\RunReportResponse
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
     * @return \Google\Service\AnalyticsData\RunReportResponse[]
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

        if(count($requests) < 1) {
            return null;
        }
        
        $reportsRequest = new BatchRunReportsRequest();
        $reportsRequest->setRequests($requests);

        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();

        $property = $requests[0]->getProperty();
        return $analyticsData->properties->batchRunReports($property, $reportsRequest);
    }

    /**
     * Get reporting report request.
     *
     * @param ReportRequestCriteria $criteria
     *
     * @return RunReportRequest
     * @throws \yii\base\InvalidConfigException
     */
    private function getReportingReportRequest(ReportRequestCriteria $criteria)
    {
        // TODO: Set attributes on the constructor instead of setters
        $request = new RunReportRequest();

        $source = Plugin::getInstance()->getSources()->getSourceById($criteria->sourceId);
        if ($source !== null) {
            $request->setProperty($source->gaPropertyId);
        }

        $this->setRequestDateRangeFromCriteria($request, $criteria);
        $this->setRequestMetricsFromCriteria($request, $criteria);
        $this->setRequestDimensionsFromCriteria($request, $criteria);

        if (!empty($criteria->orderBys)) {
            $request->setOrderBys($criteria->orderBys);
        }

        if ($criteria->offset) {
            $request->setOffset($criteria->offset);
        }

        if ($criteria->limit) {
            $request->setLimit($criteria->limit);
        }

        if ($criteria->keepEmptyRows) {
            $request->setKeepEmptyRows($criteria->keepEmptyRows);
        }

        if ($criteria->dimensionFilter) {
            $filterExpression = new FilterExpression($criteria->dimensionFilter);
            $request->setDimensionFilter($filterExpression);
        }

        if ($criteria->metricFilter) {
            $filterExpression = new FilterExpression($criteria->metricFilter);
            $request->setMetricFilter($filterExpression);
        }

        $request->setMetricAggregations(['TOTAL']);

        return $request;
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
            if (is_string($criteria->metrics)) {
                $metricString = $criteria->metrics;
                $metrics = $this->getMetricsFromString($metricString);
                $request->setMetrics($metrics);
            } else {
                $request->setMetrics($criteria->metrics);
            }
        }
    }

    /**
     * @param RunReportRequest $request
     * @param ReportRequestCriteria $criteria
     */
    private function setRequestDimensionsFromCriteria(RunReportRequest &$request, ReportRequestCriteria $criteria)
    {
        if ($criteria->dimensions) {
            if (is_string($criteria->dimensions)) {
                $dimensionString = $criteria->dimensions;
                $dimensions = $this->getDimensionsFromString($dimensionString);
                $request->setDimensions($dimensions);
            } else {
                $request->setDimensions($criteria->dimensions);
            }
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
            $dimension = new \Google\Service\AnalyticsData\Dimension();
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
            $metric = new \Google\Service\AnalyticsData\Metric();
            $metric->setName($_metric);
            $metrics[] = $metric;
        }

        return $metrics;
    }
}
