<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\Plugin;
use yii\base\Component;
use \Google_Client;
use \Google_Service_AnalyticsReporting;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_ReportRequest;
use \Google_Service_AnalyticsReporting_GetReportsRequest;
use \Google_Service_AnalyticsReporting_GetReportsResponse;

class Api4 extends Component
{
    public function parseResponse($response)
    {
        $class = get_class($response);
        switch ($class) {
            case 'Google_Service_AnalyticsReporting_GetReportsResponse';
                return $this->parseReportsResponse($response);
                break;
        }
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
                    $label = Plugin::$plugin->metadata->getDimMet($columnHeaderDimension);

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
                $col = [
                    'type' => strtolower($metricHeaderEntry['type']),
                    'label' => Craft::t('analytics', $metricHeaderEntry['name']),
                    'id' => $metricHeaderEntry['name'],
                ];

                array_push($cols, $col);
            }

            // Rows

            $rows = [];

            foreach($_report->getData()->getRows() as $_row) {

                $row = [];

                $dimensions = $_row->getDimensions();

                if($dimensions) {
                    foreach ($dimensions as $_dimension) {
                        array_push($row, $_dimension);
                    }
                }

                foreach($_row->getMetrics() as $_metric) {
                    array_push($row, $_metric->getValues()[0]);
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

    public function getAnalyticsReporting()
    {
        $client = $this->getClient();

        return new Google_Service_AnalyticsReporting($client);
    }

    private function getClient()
    {
        $token = Plugin::$plugin->getOauth()->getToken();

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
