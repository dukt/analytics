<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ExplorerController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Real-Time Visitors
     *
     * @return null
     */
    public function actionRealtimeVisitors()
    {
        try
        {
            $data = array(
                'newVisitor' => 0,
                'returningVisitor' => 0
            );

            $criteria = new Analytics_RequestCriteriaModel;
            $criteria->realtime = true;
            $criteria->metrics = 'ga:activeVisitors';
            $criteria->optParams = array('dimensions' => 'ga:visitorType');

            $results = craft()->analytics->sendRequest($criteria);

            if(!empty($results['totalResults']))
            {
                $data['total'] = $results['totalResults'];
            }

            if(!empty($results['rows'][0][1]['v']))
            {
                switch($results['rows'][0][0]['v'])
                {
                    case "RETURNING":
                    $data['returningVisitor'] = $results['rows'][0][1]['v'];
                    break;

                    case "NEW":
                    $data['newVisitor'] = $results['rows'][0][1]['v'];
                    break;
                }
            }

            if(!empty($results['rows'][1][1]['v']))
            {
                switch($results['rows'][1][0]['v'])
                {
                    case "RETURNING":
                    $data['returningVisitor'] = $results['rows'][1][1]['v'];
                    break;

                    case "NEW":
                    $data['newVisitor'] = $results['rows'][1][1]['v'];
                    break;
                }
            }

            $this->returnJson($data);
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Element Report
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionElementReport(array $variables = array())
    {
        try {
            $elementId = craft()->request->getRequiredParam('elementId');
            $locale = craft()->request->getRequiredParam('locale');
            $metric = craft()->request->getRequiredParam('metric');

            $uri = craft()->analytics->getElementUrlPath($elementId, $locale);

            if($uri)
            {
                if($uri == '__home__')
                {
                    $uri = '';
                }

                $start = date('Y-m-d', strtotime('-1 month'));
                $end = date('Y-m-d');
                $dimensions = 'ga:date';

                $optParams = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                $criteria = new Analytics_RequestCriteriaModel;
                $criteria->startDate = $start;
                $criteria->endDate = $end;
                $criteria->metrics = $metric;
                $criteria->optParams = $optParams;

                $response = craft()->analytics->sendRequest($criteria);

                $this->returnJson(array('data' => $response));
            }
            else
            {
               throw new Exception("Element doesn't support URLs.", 1);
            }
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
    public function actionCounter()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $metric = craft()->request->getParam('metrics');
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
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Table
     *
     * @return null
     */
    public function actionTable()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $dimension = craft()->request->getParam('dimensions');
            $metric = craft()->request->getParam('metrics');
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

            $this->returnJson(array(
                'table' => $tableResponse,
                'dimension' => Craft::t(craft()->analytics->getDimMet($dimension)),
                'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Area
     *
     * @return null
     */
    public function actionArea()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $dimension = craft()->request->getParam('dimensions');
            $metric = craft()->request->getParam('metrics');
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
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Pie
     *
     * @return null
     */
    public function actionPie()
    {
        $this->actionTable();
    }

    /**
     * Geo
     *
     * @return null
     */
    public function actionGeo()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $dimension = craft()->request->getParam('dimensions');
            $metric = craft()->request->getParam('metrics');
            $period = craft()->request->getParam('period');
            $start = date('Y-m-d', strtotime('-1 '.$period));
            $end = date('Y-m-d');
            $originDimension = $dimension;

            if($dimension == 'ga:city')
            {
                $dimension = 'ga:latitude, ga:longitude,'.$dimension;
            }


            $criteria = new Analytics_RequestCriteriaModel;
            $criteria->metrics = $metric;


            if($realtime)
            {
                $criteria->optParams = array('dimensions' => $dimension);
                $criteria->realtime = true;
            }
            else
            {
                $criteria->startDate = $start;
                $criteria->endDate = $end;
                $criteria->optParams = array(
                    'dimensions' => $dimension,
                    'sort' => '-'.$metric,
                    'max-results' => 20,
                    'filters' => $originDimension.'!=(not set);'.$originDimension.'!=(not provided)',
                );
            }

            $tableResponse = craft()->analytics->sendRequest($criteria);

            $this->returnJson(array(
                'table' => $tableResponse,
                'dimension' => Craft::t(craft()->analytics->getDimMet($originDimension)),
                'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}