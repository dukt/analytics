<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\models\ReportingRequestCriteria;
use yii\base\Component;
use \Google_Client;
use \Google_Service_AnalyticsReporting;
use dukt\analytics\Plugin as Analytics;
use \Google_Service_AnalyticsReporting_ReportRequest;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_GetReportsRequest;
use \Google_Service_AnalyticsReporting_GetReportsResponse;

class AnalyticsReportingApi extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Sends reports request.
     *
     * @param array $options
     *
     * @return Google_Service_AnalyticsReporting_GetReportsResponse
     */
    public function getReports(ReportingRequestCriteria $criteria)
    {
        $request = $this->getReportRequest($criteria);

        $requests = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $requests->setReportRequests(array($request));

        $client = $this->getClient();
        $analyticsReportingApi = new Google_Service_AnalyticsReporting($client);

        return $analyticsReportingApi->reports->batchGet($requests);
    }

    // Private Methods
    // =========================================================================

    private function getReportRequest(ReportingRequestCriteria $criteria)
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();

        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();
        $request->setViewId($viewId);

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($criteria->startDate);
        $dateRange->setEndDate($criteria->endDate);
        $request->setDateRanges($dateRange);

        if($criteria->metrics) {
            $metricString = $criteria->metrics;
            $metrics = $this->getMetricsFromString($metricString);
            $request->setMetrics($metrics);
        }

        if($criteria->dimensions) {
            $dimensionString = $criteria->dimensions;
            $dimensions = $this->getDimensionsFromString($dimensionString);
            $request->setDimensions($dimensions);
        }

        if($criteria->orderBys) {
            $request->setOrderBys($criteria->orderBys);
        }

        if($criteria->pageSize) {
            $request->setPageSize($criteria->pageSize);
        }

        return $request;
    }

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

    /**
     * Returns a Google client
     *
     * @return null|Google_Client
     */
    private function getClient()
    {
        $token = Analytics::$plugin->getOauth()->getToken();

        if ($token) {
            // make token compatible with Google library
            $arrayToken = [
                'created' => 0,
                'access_token' => $token->getToken(),
                'expires_in' => $token->getExpires(),
            ];

            $arrayToken = json_encode($arrayToken);

            // client
            $client = new Google_Client();
            $client->setApplicationName('Google+ PHP Starter Application');
            $client->setClientId('clientId');
            $client->setClientSecret('clientSecret');
            $client->setRedirectUri('redirectUri');
            $client->setAccessToken($arrayToken);

            return $client;
        }
    }
}
