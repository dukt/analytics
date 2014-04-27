<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

class AnalyticsController extends BaseController
{
    public function actionElementReport(array $variables = array())
    {
        $elementId = craft()->request->getParam('id');
        $element = craft()->elements->getElementById($elementId);

        $profile = craft()->analytics->getProfile();
        $start = date('Y-m-d', strtotime('-1 month'));
        $end = date('Y-m-d');
        $metrics = 'ga:sessions,ga:bounces';
        $dimensions = 'ga:date';

        $response = craft()->analytics->api()->data_ga->get(
            'ga:'.$profile['id'],
            $start,
            $end,
            $metrics,
            array(
                'dimensions' => $dimensions,
                'filters' => "ga:pagePath==/".$element->uri
            )
        );

        $this->returnJson(array('apiResponse' => $response));
    }

    public function actionCustomReport(array $variables = array())
    {
        // widget

        $id = craft()->request->getParam('id');
        $widget = craft()->dashboard->getUserWidgetById($id);


        // profile
        $profile = craft()->analytics->getProfile();

        // start / end dates
        $start = craft()->request->getParam('start');
        $end = craft()->request->getParam('end');

        if(empty($start))
        {
            $start = date('Y-m-d', strtotime('-1 month'));
        }

        if(empty($end))
        {
            $end = date('Y-m-d');
        }

        // filters
        $filters = false;
        $queryFilters = '';

        if(!empty($widget->settings['options']['filters']))
        {
            $filters = $widget->settings['options']['filters'];

            foreach($filters as $filter)
            {
                $visibility = $filter['visibility'];



                switch($filter['operator'])
                {
                    case 'exactMatch':
                    $operator = ($visibility == 'hide' ? '!=' : '==');
                    break;

                    case 'regularExpression':
                    $operator = ($visibility == 'hide' ? '!~' : '=~');
                    break;

                    case 'contains':
                    // contains or doesn't contain
                    $operator = ($visibility == 'hide' ? '!@' : '=@');
                    break;
                }

                $queryFilter = '';
                $queryFilter .= $filter['dimension'];
                $queryFilter .= $operator;
                $queryFilter .= $filter['value'];

                $queryFilters .= $queryFilter.";"; //AND
            }

            if(strlen($queryFilters) > 0)
            {
                // remove last AND
                $queryFilters = substr($queryFilters, 0, -1);
            }
        }

        // dimensions & metrics

        $metric = $widget->settings['options']['metric'];

        $options = array(
            'dimensions' => $widget->settings['options']['dimension']
        );

        if(!empty($queryFilters))
        {
            $options['filters'] = $queryFilters;
        }
        switch($widget->settings['options']['chartType'])
        {
            case 'PieChart':
                $slices = (!empty($widget->settings['options']['slices']) ? $widget->settings['options']['slices'] : 2);

                $options['sort'] = '-'.$widget->settings['options']['metric'];
                $options['max-results'] = $slices;

                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );

                break;

            default:

                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );

                break;
        }



        $this->returnJson(array(
            'widget' => $widget,
            'apiResponse' => $response
        ));
    }
}