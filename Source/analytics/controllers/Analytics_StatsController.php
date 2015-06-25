<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_StatsController extends BaseController
{
    /**
     * Area
     *
     * @return null
     */
    // Public Methods
    // =========================================================================

    public function actionGetChart()
    {
        $chart = craft()->request->getRequiredParam('chart');

        $methodName = $chart.'Chart';

        if(method_exists($this, $methodName))
        {
            $this->{$methodName}();
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Area
     *
     * @return null
     */
    private function areaChart()
    {
        try
        {
            $profile = craft()->analytics->getProfile();

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

            $this->returnJson(array(
                'area' => $chartResponse,
                'total' => $total,
                'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
                'period' => $period,
                'periodLabel' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Counter
     *
     * @return null
     */
    private function counterChart()
    {
        try
        {
            $profile = craft()->analytics->getProfile();

            $realtime = craft()->request->getParam('realtime');
            $metric = craft()->request->getParam('metric');
            $dimension = craft()->request->getParam('dimensions');
            $period = craft()->request->getParam('period');
            $start = date('Y-m-d', strtotime('-1 '.$period));
            $end = date('Y-m-d');


            // Counter

            $criteria = new Analytics_RequestCriteriaModel;
            $criteria->startDate = $start;
            $criteria->endDate = $end;
            $criteria->metrics = $metric;

            if($realtime)
            {
                $criteria->realtime = true;
            }
            else
            {
                if($dimension)
                {
                    $optParams = array('filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)');
                    $criteria->optParams = $optParams;
                }
            }

            $response = craft()->analytics->sendRequest($criteria);

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
                'label' => strtolower(Craft::t(craft()->analytics->getDimMet($metric)))
            );


            // Return JSON

            $this->returnJson(array(
                'counter' => $counter,
                'response' => $response,
                'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
                'period' => $period,
                'periodLabel' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}