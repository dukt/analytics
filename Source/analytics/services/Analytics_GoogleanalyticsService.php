<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_GoogleanalyticsService extends BaseApplicationComponent
{
    public function area()
    {
        $profile = craft()->analytics->getProfile();

        $source = craft()->request->getParam('source');
        $realtime = craft()->request->getParam('realtime');
        $dimension = craft()->request->getParam('dimension');
        $metric = craft()->request->getParam('metric');
        $period = craft()->request->getParam('period');

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

        if($realtime)
        {
            $criteria->optParams = array('dimensions' => 'rt:userType');
            $criteria->realtime = true;
        }
        else
        {
            $optParams = array(
                'dimensions' => $chartDimension,
                'sort' => $chartDimension
            );

            if($dimension)
            {
                $optParams['filters'] = $dimension.'!=(not set);'.$dimension.'!=(not provided)';
            }

            $criteria->optParams = $optParams;
        }

        $chartResponse = craft()->analytics->sendRequest($criteria);


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

        $response = craft()->analytics->sendRequest($totalCriteria);

        if(!empty($response['rows'][0][0]['f']))
        {
            $total = $response['rows'][0][0]['f'];
        }


        // Return JSON

        return [
            'area' => $chartResponse,
            'total' => $total,
            'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }

    public function table()
    {
        $profile = craft()->analytics->getProfile();

        $realtime = craft()->request->getParam('realtime');
        $dimension = craft()->request->getParam('dimension');
        $metric = craft()->request->getParam('metric');
        $period = craft()->request->getParam('period');
        $start = date('Y-m-d', strtotime('-1 '.$period));
        $end = date('Y-m-d');


        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metric;

        if($realtime)
        {
            $criteria->optParams = array('dimensions' => $dimension);
            $criteria->realtime = true;
        }
        else
        {
            $criteria->optParams = array(
                'dimensions' => $dimension,
                'sort' => '-'.$metric,
                'max-results' => 20,
                'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
            );
        }

        $tableResponse = craft()->analytics->sendRequest($criteria);

        return [
            'table' => $tableResponse,
            'dimension' => Craft::t(craft()->analytics->getDimMet($dimension)),
            'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
}
