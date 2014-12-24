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

class Analytics_ExplorerController extends BaseController
{
    /**
     * Real-Time Visitors
     */
    public function actionRealtimeVisitors()
    {
        try
        {
            $data = array(
                'newVisitor' => 0,
                'returningVisitor' => 0
            );

            $profile = craft()->analytics->getProfile();

            // visitor type

            $results = craft()->analytics->apiRealtimeGet(
                'ga:'.$profile['id'],
                'ga:activeVisitors',
                array('dimensions' => 'ga:visitorType')
            );

            //var_dump($results);

            //var_dump($results['rows']);

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
            $this->returnErrorJson($error);
        }
    }

    /**
     * Element Report
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

                $profile = craft()->analytics->getProfile();
                $start = date('Y-m-d', strtotime('-1 month'));
                $end = date('Y-m-d');
                $end = date('Y-m-d');
                $metrics = $metric;
                $dimensions = 'ga:date';

                $options = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                $data = array(
                    $profile['id'],
                    $start,
                    $end,
                    $metrics,
                    $options
                );

                $enableCache = true;

                if(craft()->config->get('disableAnalyticsCache') === null)
                {
                    if(craft()->config->get('disableAnalyticsCache', 'analytics') === true)
                    {
                        $enableCache = false;
                    }
                }
                else
                {
                    if(craft()->config->get('disableAnalyticsCache') === true)
                    {
                        $enableCache = false;
                    }
                }

                if($enableCache)
                {
                    $cacheKey = 'analytics/elementReport/'.md5(serialize($data));

                    $response = craft()->fileCache->get($cacheKey);

                    if(!$response)
                    {
                        $response = craft()->analytics->apiGet(
                            'ga:'.$profile['id'],
                            $start,
                            $end,
                            $metrics,
                            $options
                        );

                        craft()->fileCache->set($cacheKey, $response, craft()->analytics->cacheDuration());
                    }
                }
                else
                {
                    $response = craft()->analytics->apiGet(
                        'ga:'.$profile['id'],
                        $start,
                        $end,
                        $metrics,
                        $options
                    );
                }

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

            $filters = false;

            if($dimension)
            {
                $filters = $dimension.'!=(not set);'.$dimension.'!=(not provided)';
            }

            if($realtime)
            {
                $response = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    $metric,
                    array()
                );

                if(!empty($response['rows'][0][0]['v']))
                {
                    $count = $response['rows'][0][0]['v'];
                }
                else
                {
                    $count = 0;
                }

                $counter = array(
                    'count' => $count,
                    'label' => strtolower(Craft::t(craft()->analytics->getDimMet($metric)))
                );
            }
            else
            {
                $options = array();

                if($filters)
                {
                    $options['filters'] = $filters;
                }

                $response = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );


                if(!empty($response['rows'][0][0]['v']))
                {
                    $count = $response['rows'][0][0]['v'];
                }
                else
                {
                    $count = 0;
                }

                $counter = array(
                    'count' => $count
                );
            }

            $this->returnJson(array(
                'counter' => $counter,
                'metric' => Craft::t(craft()->analytics->getDimMet($metric)),
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

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

            if($realtime)
            {
                $tableResponse = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    $metric,
                    array('dimensions' => $dimension)
                );
            }
            else
            {
                $tableResponse = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    array(
                        'dimensions' => $dimension,
                        'sort' => '-'.$metric,
                        'max-results' => 20,
                        'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
                    )
                );
            }

            foreach($tableResponse['rows'] as $k => $row)
            {
                $tableResponse['rows'][$k][0]['f'] = Craft::t($tableResponse['rows'][$k][0]['f']);
            }

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

    public function actionArea()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
            $dimension = craft()->request->getParam('dimensions');
            $metric = craft()->request->getParam('metrics');
            $period = craft()->request->getParam('period');

            $filters = false;

            if($dimension)
            {
                $filters = $dimension.'!=(not set);'.$dimension.'!=(not provided)';
            }

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

            if($realtime)
            {
                $chartResponse = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    $metric,
                    array('dimensions' => 'rt:userType')
                );
            }
            else
            {
                $options = array(
                    'dimensions' => $chartDimension,
                    'sort' => $chartDimension
                );

                if($filters)
                {
                    $options['filters'] = $filters;
                }

                $chartResponse = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );
            }

            $total = 0;

            $options = array();

            if($filters)
            {
                $options['filters'] = $filters;
            }

            $response = craft()->analytics->apiGet(
                'ga:'.$profile['id'],
                $start,
                $end,
                $metric,
                $options
            );

            if(!empty($response['rows'][0][0]['v']))
            {
                $total = $response['rows'][0][0]['v'];
            }

            // return json

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

    public function actionPie()
    {
        $this->actionTable();
    }

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
            $cityMode = false;
            $originDimension = $dimension;

            if($dimension == 'ga:city')
            {
                $cityMode = true;
                $dimension = 'ga:latitude, ga:longitude,'.$dimension;
            }

            if($realtime)
            {
                $tableResponse = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    $metric,
                    array('dimensions' => $dimension)
                );
            }
            else
            {
                $tableResponse = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    array(
                        'dimensions' => $dimension,
                        'sort' => '-'.$metric,
                        'max-results' => 20,
                    )
                );
            }

            foreach($tableResponse['rows'] as $k => $row)
            {
                $tableResponse['rows'][$k][0]['f'] = Craft::t($tableResponse['rows'][$k][0]['f']);

                if($dimension == 'ga:continent')
                {
                    $tableResponse['rows'][$k][0]['v'] = craft()->analytics->getContinentCode($tableResponse['rows'][$k][0]['v']);
                }

                if($dimension == 'ga:subContinent')
                {
                    $tableResponse['rows'][$k][0]['v'] = craft()->analytics->getSubContinentCode($tableResponse['rows'][$k][0]['v']);
                }
            }

            // if($cityMode)
            // {
            //     $tableResponse['columns'][1]['dataType'] = 'NUMBER';
            //     $tableResponse['columns'][]['dataType'] = 'NUMBER';
            // }

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

    public function actionSaveWidgetState()
    {
        $widgetId = craft()->request->getPost('id');

        $formerWidget = craft()->dashboard->getUserWidgetById($widgetId);

        if($formerWidget)
        {
            $widgetSettings = craft()->request->getPost('settings');

            if(!empty($formerWidget->settings['colspan']))
            {
                $widgetSettings['colspan'] = $formerWidget->settings['colspan'];
            }

            if(empty($widgetSettings['colspan']))
            {
                $widgetSettings['colspan'] = 1;
            }

            $widget = new WidgetModel();
            $widget->id = $widgetId;
            $widget->type = 'Analytics_Explorer';
            $widget->settings = $widgetSettings;

            if (craft()->dashboard->saveUserWidget($widget))
            {
                $this->returnJson(true);
            }
            else
            {
                $this->returnErrorJson('Couldn’t save widget');
            }
        }
        else
        {
            $this->returnErrorJson('Couldn’t save widget');
        }
    }

    public function actionConsole(array $variables = array())
    {
        if(empty($variables['profileId']))
        {
            $profile = craft()->analytics->getProfile();
            $variables['profileId'] = $profile['id'];
        }

        $this->renderTemplate('analytics/console', $variables);
    }

    public function actionConsoleSend()
    {
        // params
        $profileId = craft()->request->getParam('profileId');
        $start = craft()->request->getParam('start');
        $end = craft()->request->getParam('end');
        $metrics = craft()->request->getParam('metrics');
        $options = craft()->request->getParam('options');

        // send request
        $response = craft()->analytics->apiGet(
            'ga:'.$profileId,
            $start,
            $end,
            $metrics,
            $options
        );

        // set route variables
        craft()->urlManager->setRouteVariables(array(
            'profileId' => $profileId,
            'start' => $start,
            'end' => $end,
            'metrics' => $metrics,
            'options' => $options,
            'response' => $response
        ));
    }
}