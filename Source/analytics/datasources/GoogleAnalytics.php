<?php

namespace Dukt\Analytics\DataSources;

use Craft\Craft;
use Craft\Analytics_RequestCriteriaModel;

class GoogleAnalytics extends BaseDataSource
{
    public function getSettingsHtml($variables = [])
    {
        $dimensions = Craft::app()->analytics->getDimensions();
        $metrics = Craft::app()->analytics->getMetrics();

        $variables['dimensionsOptions'] = $dimensions;
        $variables['metricsOptions'] = $metrics;

        return Craft::app()->templates->render('analytics/widgets/stats/_googleAnalyticsSettings', $variables);
    }

    public function getChartData($options)
    {
        $chart = $options['chart'];

        return $this->{$chart}($options);
    }

    public function area($requestData)
    {
        $profile = Craft::app()->analytics->getProfile();

        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        switch($period)
        {
            case 'year':
                $chartDimension = 'ga:yearMonth';
                $start = date('Y-m-01', strtotime('-1 '.$period));
                $end = date('Y-m-d');
                break;

            default:
                $chartDimension = 'ga:date';
                $start = date('Y-m-d', strtotime('-1 '.$period));
                $end = date('Y-m-d');
        }


        // Chart

        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        $optParams = array(
            'dimensions' => $chartDimension,
            'sort' => $chartDimension
        );

        if($dimension)
        {
            $optParams['filters'] = $dimension.'!=(not set);'.$dimension.'!=(not provided)';
        }

        $criteria->optParams = $optParams;

        $chartResponse = Craft::app()->analytics->sendRequest($criteria);


        // Total

        $total = 0;

        $totalCriteria = new Analytics_RequestCriteriaModel;
        $totalCriteria->startDate = $start;
        $totalCriteria->endDate = $end;
        $totalCriteria->metrics = $metric;

        if(isset($criteria->optParams['filters']))
        {
            $totalCriteria->optParams = array('filters' => $criteria->optParams['filters']);
        }

        $response = Craft::app()->analytics->sendRequest($totalCriteria);

        if(!empty($response['rows'][0][0]['f']))
        {
            $total = $response['rows'][0][0]['f'];
        }


        // Return JSON

        return [
            'type' => 'area',
            'chart' => $chartResponse,
            'total' => $total,
            'metric' => Craft::t(Craft::app()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function counter($requestData)
    {
        $profile = Craft::app()->analytics->getProfile();

        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');


        // Counter

        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        if($dimension)
        {
            $optParams = array('filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)');
            $criteria->optParams = $optParams;
        }

        $response = Craft::app()->analytics->sendRequest($criteria);

        if(!empty($response['rows'][0][0]['f']))
        {
            $count = $response['rows'][0][0]['f'];
        }
        else
        {
            $count = 0;
        }

        $counter = array(
            'count' => $count,
            'label' => strtolower(Craft::t(Craft::app()->analytics->getDimMet($metric)))
        );


        // Return JSON

        return [
            'type' => 'counter',
            'counter' => $counter,
            'response' => $response,
            'metric' => Craft::t(Craft::app()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function pie($requestData)
    {
        $profile = Craft::app()->analytics->getProfile();

        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');

        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
        );

        $tableResponse = Craft::app()->analytics->sendRequest($criteria);

        return [
            'type' => 'pie',
            'chart' => $tableResponse,
            'dimension' => Craft::t(Craft::app()->analytics->getDimMet($dimension)),
            'metric' => Craft::t(Craft::app()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function table($requestData)
    {
        $profile = Craft::app()->analytics->getProfile();

        $period = (isset($requestData['period']) ? $requestData['period'] : null);
        $dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
        $metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');

        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
        );

        $tableResponse = Craft::app()->analytics->sendRequest($criteria);

        return [
            'type' => 'table',
            'chart' => $tableResponse,
            'dimension' => Craft::t(Craft::app()->analytics->getDimMet($dimension)),
            'metric' => Craft::t(Craft::app()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function geo($requestData)
    {
        $profile = Craft::app()->analytics->getProfile();

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


        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->metrics = $metric;

        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->optParams = array(
            'dimensions' => $dimension,
            'sort' => '-'.$metric,
            'max-results' => 20,
            'filters' => $originDimension.'!=(not set);'.$originDimension.'!=(not provided)',
        );

        $tableResponse = Craft::app()->analytics->sendRequest($criteria);

        return [
            'type' => 'geo',
            'chart' => $tableResponse,
            'dimensionRaw' => $originDimension,
            'dimension' => Craft::t(Craft::app()->analytics->getDimMet($originDimension)),
            'metric' => Craft::t(Craft::app()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
}
