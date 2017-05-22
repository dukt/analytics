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
     * Get report.
     *
     * @param ReportingRequestCriteria $criteria
     *
     * @return mixed
     */
    public function getReport(ReportingRequestCriteria $criteria)
    {
        $response = $this->getReports([$criteria]);

        $reports = $this->parseReportsResponse($response);

        if(isset($reports[0]))
        {
            return $reports[0];
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Get reports.
     *
     * @param array $criterias
     *
     * @return Google_Service_AnalyticsReporting_GetReportsResponse
     */
    private function getReports($criterias)
    {
        $requests = [];

        foreach($criterias as $criteria) {
            $request = $this->getReportRequest($criteria);
            array_push($requests, $request);
        }

        $reportsRequest = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $reportsRequest->setReportRequests($requests);

        $client = $this->getClient();
        $analyticsReportingApi = new Google_Service_AnalyticsReporting($client);

        return $analyticsReportingApi->reports->batchGet($reportsRequest);
    }

    /**
     * Parse reporting reports response.
     *
     * @param Google_Service_AnalyticsReporting_GetReportsResponse $response
     *
     * @return array
     */
    private function parseReportsResponse(Google_Service_AnalyticsReporting_GetReportsResponse $response)
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
