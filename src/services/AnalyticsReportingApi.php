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
use dukt\analytics\models\RequestCriteria;
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
     * @return array
     */
    public function sendReportsRequest(ReportingRequestCriteria $criteria)
    {
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();

        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();
        $request->setViewId($viewId);

        $dateRange = $this->getAnalyticsReportingDateRange($criteria->startDate, $criteria->endDate);
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

        $requests = $this->getAnalyticsReportingGetReportsRequest(array($request));
        $response = $this->getAnalyticsReporting()->reports->batchGet($requests);

        return $this->parseReportsResponse($response);
    }

    public function parseReportsResponse(Google_Service_AnalyticsReporting_GetReportsResponse $response)
    {
        $reports = [];

        foreach ($response->getReports() as $_report) {


            $columnHeader = $_report->getColumnHeader();
            $columnHeaderDimensions = $columnHeader->getDimensions();
            $metricHeaderEntries = $columnHeader->getMetricHeader()->getMetricHeaderEntries();


            // Columns

            $cols = [];

            if($columnHeaderDimensions) {
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

            foreach($metricHeaderEntries as $metricHeaderEntry) {
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

            foreach($_report->getData()->getRows() as $_row) {

                $colIndex = 0;
                $row = [];

                $dimensions = $_row->getDimensions();

                if($dimensions) {
                    foreach ($dimensions as $_dimension) {

                        $value = $_dimension;

                        if($columnHeaderDimensions) {
                            if(isset($columnHeaderDimensions[$colIndex])) {
                                switch($columnHeaderDimensions[$colIndex])
                                {
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

                foreach($_row->getMetrics() as $_metric) {
                    array_push($row, $_metric->getValues()[0]);
                    $colIndex++;
                }

                array_push($rows, $row);
            }

            $totals = $_report->getData()->getTotals()[0]->getValues();

            $report = [
                'cols' => $cols,
                'rows' => $rows,
                'totals' => $totals
            ];

            array_push($reports, $report);
        }

        return $reports;
    }

    public function getAnalyticsReportingGetReportsRequest($requests)
    {
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);

        return $body;
    }

    public function getAnalyticsReportingReportRequest($viewId, $dateRanges, $metrics, $dimensions)
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRanges);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);

        return $request;
    }

    public function getDimensionsFromString($string)
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

    public function getMetricsFromString($string)
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

    public function getAnalyticsReportingDateRange($startDate, $endDate)
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        return $dateRange;
    }

    /**
     * Returns the Google Analytics Reporting API object (API v4)
     *
     * @return bool|Google_Service_AnalyticsReporting
     */
    public function getAnalyticsReporting()
    {
        $client = $this->getClient();

        return new Google_Service_AnalyticsReporting($client);
    }

    // Private Methods
    // =========================================================================

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
