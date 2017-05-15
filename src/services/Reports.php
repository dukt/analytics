<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\StringHelper;
use yii\base\Component;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\Plugin as Analytics;

class Reports extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a report for any chart type (Area,  Counter,  Pie,  Table,  Geo)
     *
     * @param array $options
     *
     * @return array
     */
    public function getReport($options)
    {
        $chart = $options['chart'];

        $methodName = 'get'.ucfirst($chart).'Report';

        if(method_exists($this, $methodName))
        {
            return $this->{$methodName}($options);
        }
        else
        {
            throw new Exception("Chart type `".$chart."` not supported.");
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

        // Prepare report request
        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();
        $dateRange = Analytics::$plugin->getApi4()->getAnalyticsReportingDateRange($startDate, $endDate);
        $dimensions = Analytics::$plugin->getApi4()->getDimensionsFromString($dimensionString);
        $metrics = Analytics::$plugin->getApi4()->getMetricsFromString($metricString);

        // Report request
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setOrderBys([
            ["fieldName" => $dimensionString, "orderType" => 'VALUE', "sortOrder" => 'ASCENDING']
        ]);

        $requests = Analytics::$plugin->getApi4()->getAnalyticsReportingGetReportsRequest(array($request));
        $response = Analytics::$plugin->getApi4()->getAnalyticsReporting()->reports->batchGet($requests);
        $reports = Analytics::$plugin->getApi4()->parseReportsResponse($response);

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


        // Prepare report request
        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();
        $dateRange = Analytics::$plugin->getApi4()->getAnalyticsReportingDateRange($startDate, $endDate);
        $metrics = Analytics::$plugin->getApi4()->getMetricsFromString($metricString);


        // Report request
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);

        $requests = Analytics::$plugin->getApi4()->getAnalyticsReportingGetReportsRequest(array($request));
        $response = Analytics::$plugin->getApi4()->getAnalyticsReporting()->reports->batchGet($requests);
        $reports = Analytics::$plugin->getApi4()->parseReportsResponse($response);

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


        // Return JSON

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
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');

        $criteria = new RequestCriteria;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
        );

        $tableResponse = Analytics::$plugin->getApi()->sendRequest($criteria);

        return [
            'type' => 'pie',
            'chart' => $tableResponse,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metric)),
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
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');

        $criteria = new RequestCriteria;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
        );

        $tableResponse = Analytics::$plugin->getApi()->sendRequest($criteria);

        return [
            'type' => 'table',
            'chart' => $tableResponse,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($dimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metric)),
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
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');

        $originDimension = $dimension;

        if($dimension == 'ga:city')
        {
            $dimension = 'ga:latitude, ga:longitude,'.$dimension;
        }


        $criteria = new RequestCriteria;
        $criteria->metrics = $metric;

        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $originDimension.'!=(not set);'.$originDimension.'!=(not provided)',
        );

        $tableResponse = Analytics::$plugin->getApi()->sendRequest($criteria);

        return [
            'type' => 'geo',
            'chart' => $tableResponse,
            'dimensionRaw' => $originDimension,
            'dimension' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($originDimension)),
            'metric' => Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('analytics', 'this '.$period)
        ];
    }

    /**
     * Deprecated
     */

}
