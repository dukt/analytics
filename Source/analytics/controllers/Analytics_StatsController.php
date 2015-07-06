<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_StatsController extends BaseController
{
    public function actionTestMeta()
    {
        $dimensions = array();
        $metrics = array();
        $r = craft()->analytics->getApiDimensionsMetrics()->items;

        foreach($r as $item)
        {
            $attr = $item->attributes;

            if($attr['type'] == 'METRIC')
            {
                $metrics[] = $item;
            }
            elseif($attr['type'] == 'DIMENSION')
            {
                $dimensions[] = $item;
            }

            if($item->id == 'ga:goalXXCompletions')
            {
                echo '<pre>';
                var_dump($item);
                echo '</pre>';
                die();
            }
        }

        echo '<h1>Dimensions</h1>';
        echo '<ul>';
        foreach($dimensions as $item)
        {
            echo '<li>'.$item['id'].'</li>';
        }

        echo '</ul>';

        echo '<h1>Metrics</h1>';
        echo '<ul>';
        foreach($metrics as $item)
        {
            echo '<li>'.$item['id'].'</li>';
        }
        echo '</ul>';

        die();
    }
    /**
     * Area
     *
     * @return null
     */
    // Public Methods
    // =========================================================================

    public function actionTest()
    {
        $start = date('Y-m-d', strtotime('-7 days'));
        $end = date('Y-m-d', strtotime('+1 day'));

        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        $criteria->after = $start;
        $criteria->before = $end;
        $criteria->order = 'postDate DESC';


        $elements = $criteria->find();

        $data = array();

        foreach($elements as $element)
        {
            $date = $element->postDate->format('Y-m-d');

            if(isset($data[$date]))
            {
                $data[$date]++;
            }
            else
            {
                $data[$date] = 1;
            }
        }
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
            $element = craft()->request->getParam('element');

            switch ($element)
            {
                case 'entries':
                    $elementType = ElementType::Entry;
                    break;

                case 'users':
                    $elementType = ElementType::User;
                    break;

                default:
                    $elementType = null;
                    break;
            }

            switch($period)
            {
                case 'year':
                    $metric = 'ga:yearMonth';
                    $start = date('Y-m-01', strtotime('-1 '.$period));
                    $end = date('Y-m-d');
                    break;

                default:
                    $metric = 'ga:date';
                    $start = date('Y-m-d', strtotime('-1 '.$period));
                    $end = date('Y-m-d');
            }

            $chartResponse = array(
                'cols' => array(
                    array(
                        'dataType' => "STRING",
                        'id' => $metric,
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
                'rows' => array()
            );

            $criteria = craft()->elements->getCriteria($elementType);
            $criteria->after = $start;
            $criteria->before = $end;



            switch ($elementType)
            {
                case ElementType::Entry:
                    $criteria->order = 'postDate DESC';
                    break;

                case ElementType::User:
                    $criteria->order = 'dateCreated DESC';
                    break;
            }

            $elements = $criteria->find();

            $data = array();

            switch($period)
            {
                case 'year':

                    $months = floor((strtotime($end) - strtotime($start)) / 60 / 60 / 24 / 30) + 1;

                    for($month = 0; $month < $months; $month++)
                    {
                        $time = date('Ym', strtotime('-'.$month. ' month', strtotime($end)));

                        foreach($elements as $element)
                        {
                            switch ($elementType)
                            {
                                case ElementType::Entry:
                                    $date = $element->postDate->format('Ym');
                                    break;

                                case ElementType::User:
                                    $date = $element->dateCreated->format('Ym');
                                    break;
                            }


                            if($time == $date)
                            {
                                if(isset($data[$date]))
                                {
                                    $data[$date]++;
                                }
                                else
                                {
                                    $data[$date] = 1;
                                }
                            }
                        }

                        if(!isset($data[$time]))
                        {
                            $data[$time] = 0;
                        }
                    }

                    break;

                default:

                    $days = floor((strtotime($end) - strtotime($start)) / 60 / 60 / 24);

                    for($day = 0; $day < $days; $day++)
                    {
                        $time = date('Ymd', strtotime('-'.$day. ' day', strtotime($end)));

                        foreach($elements as $element)
                        {
                            switch ($elementType)
                            {
                                case ElementType::Entry:
                                    $date = $element->postDate->format('Ymd');
                                    break;

                                case ElementType::User:
                                    $date = $element->dateCreated->format('Ymd');
                                    break;
                            }

                            if($time == $date)
                            {
                                if(isset($data[$date]))
                                {
                                    $data[$date]++;
                                }
                                else
                                {
                                    $data[$date] = 1;
                                }
                            }
                        }

                        if(!isset($data[$time]))
                        {
                            $data[$time] = 0;
                        }
                    }
            }

            foreach($data as $date => $total)
            {
                $row = array(
                    array('v' => (string) $date, 'f' => (string) $date),
                    array('v' => $total, 'f' => (string) $total),
                );

                $chartResponse['rows'][] = $row;
            }

            // Total

            $total = 0;


            // Return JSON

            $this->returnJson(array(
                'area' => $chartResponse,
                'total' => $total,
                'metric' => 'ga:users',
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