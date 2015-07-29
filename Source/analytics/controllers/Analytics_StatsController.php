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
        try
        {
            $source = craft()->request->getParam('source');

            $response = craft()->{'analytics_'.$source}->area();

            $this->returnJson($response);
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
    private function tableChart()
    {
        try
        {
            $source = craft()->request->getParam('source');

            $response = craft()->{'analytics_'.$source}->table();

            $this->returnJson($response);
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