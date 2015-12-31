<?php

namespace Dukt\Analytics\DataSources;

use Craft\Craft;
use Craft\Analytics_RequestCriteriaModel;
use Craft\StringHelper;

class GoogleAnalytics extends BaseDataSource
{
    public function getSettingsHtml($variables = [])
    {
        $chartTypes = ['area', 'counter', 'pie', 'table', 'geo'];

        $selectOptions = [];

        foreach($chartTypes as $chartType)
        {
            $selectOptions[$chartType] = $this->getOptions($chartType);
        }

        $variables['selectOptions'] = $selectOptions;

        return Craft::app()->templates->render('analytics/_components/datasources/googleanalytics', $variables);
    }

    public function getChartData($options)
    {
        $chart = $options['chart'];

        return $this->{$chart}($options);
    }

    private function getOptions($chart)
    {
        switch($chart)
        {
            case 'geo':

                $options = [
                    'dimensions' => Craft::app()->analytics_meta->getSelectDimensionOptions(['ga:city', 'ga:country', 'ga:continent', 'ga:subContinent']),
                    'metrics' => Craft::app()->analytics_meta->getSelectMetricOptions()
                ];

                break;

            default:

                $options = [
                    'dimensions' => Craft::app()->analytics_meta->getSelectDimensionOptions(),
                    'metrics' => Craft::app()->analytics_meta->getSelectMetricOptions()
                ];
        }

        return $options;
    }

    public function area($requestData)
    {
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
            'metric' => Craft::t(Craft::app()->analytics_meta->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('This '.$period)
        ];
    }

    public function counter($requestData)
    {
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
            'label' => StringHelper::toLowerCase(Craft::t(Craft::app()->analytics_meta->getDimMet($metric)))
        );


        // Return JSON

        return [
            'type' => 'counter',
            'counter' => $counter,
            'response' => $response,
            'metric' => Craft::t(Craft::app()->analytics_meta->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function pie($requestData)
    {
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
            'dimension' => Craft::t(Craft::app()->analytics_meta->getDimMet($dimension)),
            'metric' => Craft::t(Craft::app()->analytics_meta->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function table($requestData)
    {
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
            'dimension' => Craft::t(Craft::app()->analytics_meta->getDimMet($dimension)),
            'metric' => Craft::t(Craft::app()->analytics_meta->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function geo($requestData)
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
            'dimension' => Craft::t(Craft::app()->analytics_meta->getDimMet($originDimension)),
            'metric' => Craft::t(Craft::app()->analytics_meta->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
}
