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

    public function actionTest()
    {
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $elements = $criteria->find();

        foreach($elements as $element)
        {
            echo '<h2>'.$element->title.'</h2>';
        }

        die();
    }

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
        $source = craft()->request->getParam('source');

        switch($source)
        {
            case 'craft':
                $this->areaChartCraft();
                break;

            case 'googleanalytics':
                $this->areaChartGoogleAnalytics();
                break;
        }
    }

    private function areaChartCraft()
    {
        try
        {
            $period = craft()->request->getParam('period');
            $metric = craft()->request->getParam('metric');

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


            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $elements = $criteria->find();

            $chartResponse = array(
                'cols' => array(
                    array(
                        'dataType' => "STRING",
                        'id' => "ga:date",
                        'label' => "",
                        'type' => "date",
                    ),
                    array(
                        'dataType' => "INTEGER",
                        'id' => "ga:users",
                        'label' => "Users",
                        'type' => "number",
                    ),
                ),
                'rows' => array(
                    array(
                        array(
                            'f' => "20150621",
                            'v' => "20150621"
                        ),
                        array(
                            'f' => "15",
                            'v' => 15
                        ),
                    ),
                    array(
                        array(
                            'f' => "20150622",
                            'v' => "20150622"
                        ),
                        array(
                            'f' => "23",
                            'v' => 23
                        ),
                    )
                )
            );


            // Total

            $total = 0;


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
     * Area
     *
     * @return null
     */
    private function areaChartGoogleAnalytics()
    {
        try
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


    /**
     * Pie
     *
     * @return null
     */
    public function pieChart()
    {
        $this->tableChart();
    }

    /**
     * Table
     *
     * @return null
     */
    public function tableChart()
    {
        try
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

            $this->returnJson(array(
                'table' => $tableResponse,
                'dimension' => Craft::t(craft()->analytics->getDimMet($dimension)),
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
     * Geo
     *
     * @return null
     */
    public function geoChart()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $dimension = craft()->request->getParam('dimension');
            $metric = craft()->request->getParam('metric');
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