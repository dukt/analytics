<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\StringHelper;
use dukt\analytics\models\ReportingRequestCriteria;
use yii\base\Component;
use dukt\analytics\Plugin as Analytics;

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
        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();

        $ids = $viewId;
        $metrics = $request['metrics'];
        $optParams = $request['optParams'];

        $response = Analytics::$plugin->getAnalyticsApi()->googleAnalytics()->data_realtime->get($ids, $metrics, $optParams);

        return Analytics::$plugin->getAnalyticsApi()->parseReportResponse($response);
    }

    /**
     * Returns an element report.
     *
     * @param array $request
     *
     * @return array
     */
    public function getElementReport(array $request)
    {
        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();

        $ids = $viewId;
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $metrics = $request['metrics'];
        $optParams = $request['optParams'];
        $enableCache = (isset($request['enableCache']) ? $request['enableCache'] : null);

        $cacheId = ['api.apiGetGAData', [$ids, $startDate, $endDate, $metrics, $optParams]];
        $response = Analytics::$plugin->cache->get($cacheId);

        if(!$response)
        {
            if(!$optParams)
            {
                $optParams = [];
            }

            $response = Analytics::$plugin->getAnalyticsApi()->googleAnalytics()->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);

            Analytics::$plugin->cache->set($cacheId, $response, null, null, $enableCache);
        }

        return Analytics::$plugin->getAnalyticsApi()->parseReportResponse($response);
    }

    /**
     * Returns a report for any chart type (Area,  Counter,  Pie,  Table,  Geo)
     *
     * @param array $options
     *
     * @return array
     * @throws \Exception
     */
    public function getReport(array $options)
    {
        $chart = $options['chart'];

        switch($chart) {
            case 'area':
                return $this->getAreaReport($options);
            case 'counter':
                return $this->getCounterReport($options);
            case 'pie':
                return $this->getPieReport($options);
            case 'table':
                return $this->getTableReport($options);
            case 'geo':
                return $this->getGeoReport($options);
            default:
                throw new \Exception("Chart type `".$chart."` not supported.");
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns an area report
     *
     * @param array $requestData
     *
     * @return array
     */
    private function getAreaReport($requestData)
    {
        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $metricString = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        switch($period)
        {
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

        $criteria = new ReportingRequestCriteria;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;
        $criteria->orderBys = [
            ["fieldName" => $dimensionString, "orderType" => 'VALUE', "sortOrder" => 'ASCENDING']
        ];

        $reports = Analytics::$plugin->getAnalyticsReportingApi()->sendReportsRequest($criteria);

        $report = $reports[0];
        $total = $report['totals'][0];

        return [
            'type' => 'area',
            'chart' => $report,
            'total' => $total,
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'This '.$period)
        ];
    }

    /**
     * Returns a counter report
     *
     * @param array $requestData
     *
     * @return array
     */
    private function getCounterReport($requestData)
    {
        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $metricString = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportingRequestCriteria;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;

        $reports = Analytics::$plugin->getAnalyticsReportingApi()->sendReportsRequest($criteria);

        $report = $reports[0];
        $total = 0;

        if(!empty($report['totals'][0])) {
            $total = $report['totals'][0];
        }

        $counter = array(
            'type' => $report['cols'][0]['type'],
            'value' => $total,
            'label' => StringHelper::toLowerCase(Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)))
        );

        return [
            'type' => 'counter',
            'counter' => $counter,
            'response' => $report,
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a pie report
     *
     * @param array $requestData
     *
     * @return array
     */
    private function getPieReport($requestData)
    {
        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimensionString = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metricString = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);
        $startDate = date('Y-m-d', strtotime('-1 '.$period));
        $endDate = date('Y-m-d');

        $criteria = new ReportingRequestCriteria;
        $criteria->startDate = $startDate;
        $criteria->endDate = $endDate;
        $criteria->metrics = $metricString;
        $criteria->dimensions = $dimensionString;

        $reports = Analytics::$plugin->getAnalyticsReportingApi()->sendReportsRequest($criteria);
        $report = $reports[0];

        return [
            'type' => 'pie',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a table report
     *
     * @param array $requestData
     *
     * @return array
     */
    private function getTableReport($requestData)
    {
        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimensionString = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metricString = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $criteria = new ReportingRequestCriteria;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reports = Analytics::$plugin->getAnalyticsReportingApi()->sendReportsRequest($criteria);

        $report = $reports[0];

        return [
            'type' => 'table',
            'chart' => $report,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimensionString)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Returns a geo report
     *
     * @param array $requestData
     *
     * @return array
     */
    private function getGeoReport($requestData)
    {
        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimensionString = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metricString = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $originDimension = $dimensionString;

        if($dimensionString == 'ga:city')
        {
            $dimensionString = 'ga:latitude,ga:longitude,'.$dimensionString;
        }

        $criteria = new ReportingRequestCriteria;
        $criteria->dimensions = $dimensionString;
        $criteria->metrics = $metricString;
        $criteria->startDate = date('Y-m-d', strtotime('-1 '.$period));
        $criteria->endDate = date('Y-m-d');

        $reports = Analytics::$plugin->getAnalyticsReportingApi()->sendReportsRequest($criteria);
        $report = $reports[0];

        return [
            'type' => 'geo',
            'chart' => $report,
            'dimensionRaw' => $originDimension,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($originDimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metricString)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }
}
