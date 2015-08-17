<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_TestController extends BaseController
{
    // Public Methods
    // =========================================================================

    public function actionMeta()
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
}
