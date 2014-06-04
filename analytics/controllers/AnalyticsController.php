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
    private $handle = 'google';

    private $scopes = array(
        'userinfo_profile',
        'userinfo_email',
        'analytics'
    );

    private $params = array(
        'access_type' => 'offline',
        'approval_prompt' => 'force'
    );

    /**
     * Connect
     */
    public function actionConnect()
    {
        // redirect
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

        // session vars
        craft()->oauth->sessionClean();
        craft()->httpSession->add('oauth.plugin', 'analytics');
        craft()->httpSession->add('oauth.redirect', $redirect);
        craft()->httpSession->add('oauth.scopes', $this->scopes);
        craft()->httpSession->add('oauth.params', $this->params);

        // redirect

        $this->redirect(UrlHelper::getActionUrl('oauth/public/connect/', array(
            'provider' => $this->handle
        )));
    }

    /**
     * Disconnect
     */
    public function actionDisconnect()
    {

        // get plugin settings
        $plugin = craft()->plugins->getPlugin('analytics');
        $settings = $plugin->getSettings();

        // remove token from plugin settings
        $settings['token'] = null;

        craft()->plugins->savePluginSettings($plugin, $settings);

        // set notice
        craft()->userSession->setNotice(Craft::t("Disconnected from Google Analytics."));

        // redirect
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $this->redirect($redirect);
    }








    public function actionGetCountReport()
    {
        try {
            $profile = craft()->analytics->getProfile();

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

            $cacheKey = 'analytics/getCountReport/'.md5(serialize(array($profile['id'], $start, $end)));

            $response = craft()->fileCache->get($cacheKey);

            if(!$response)
            {
                $response = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    'ga:visits, ga:entrances, ga:exits, ga:pageviews, ga:timeOnPage, ga:exitRate, ga:entranceRate, ga:pageviewsPerVisit, ga:avgTimeOnPage, ga:visitBounceRate'
                );

                craft()->fileCache->set($cacheKey, $response, craft()->analytics->cacheExpiry());
            }

            $row = array_pop($response['rows']);

            $counts = array(
                'visits'            => (!empty($row['ga:visits']) ? $row['ga:visits'] : 0),
                'entrances'         => (!empty($row['ga:entrances']) ? $row['ga:entrances'] : 0),
                'exits'             => (!empty($row['ga:exits']) ? $row['ga:exits'] : 0),
                'pageviews'         => (!empty($row['ga:pageviews']) ? $row['ga:pageviews'] : 0),
                'timeOnPage'        => (!empty($row['ga:timeOnPage']) ? $row['ga:timeOnPage'] : 0),
                'exitRate'          => (!empty($row['ga:exitRate']) ? round($row['ga:exitRate'], 2)."%" : '0%'),
                'entranceRate'      => (!empty($row['ga:entranceRate']) ? $row['ga:entranceRate'] : 0),
                'pageviewsPerVisit' => (!empty($row['ga:pageviewsPerVisit']) ? round($row['ga:pageviewsPerVisit'], 2) : '0.00'),
                'avgTimeOnPage'     => (!empty($row['ga:avgTimeOnPage']) ? craft()->analytics->secondMinute($row['ga:avgTimeOnPage']) : '00:00'),
                'visitBounceRate'   => (!empty($row['ga:visitBounceRate']) ? round($row['ga:visitBounceRate'], 2)."%" : '0%'),
            );

            $html = craft()->templates->render('analytics/widgets/report/_counts', array(
                'counts' => $counts
            ));

            $this->returnJson(array(
                'html' => $html
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    public function actionRealtime()
    {
        $data = array(
            'total' => '0',
            'visitorType' => array(
                'newVisitor' => 0,
                'returningVisitor' => 0
            ),
            'content' =>  array(),
            'sources' => array(),
            'countries' => array(),
            'errors' => false
        );

        try {
            $profile = craft()->analytics->getProfile();

            if(!empty($profile['id']))
            {

                // visitor type

                $results = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:visitorType')
                );

                //var_dump($results->rows);

                if(!empty($results['totalResults']))
                {
                    $data['total'] = $results['totalResults'];
                }

                if(!empty($results->rows[0][1]))
                {
                    switch($results->rows[0][0])
                    {
                        case "RETURNING":
                        $data['visitorType']['returningVisitor'] = $results->rows[0][1];
                        break;

                        case "NEW":
                        $data['visitorType']['newVisitor'] = $results->rows[0][1];
                        break;
                    }
                }

                if(!empty($results->rows[1][1]))
                {
                    switch($results->rows[1][0])
                    {
                        case "RETURNING":
                        $data['visitorType']['returningVisitor'] = $results->rows[1][1];
                        break;

                        case "NEW":
                        $data['visitorType']['newVisitor'] = $results->rows[1][1];
                        break;
                    }
                }


                // content

                $results = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:pagePath')
                );

                if(!empty($results->rows))
                {
                    foreach($results->rows as $row)
                    {
                        $data['content'][$row[0]] = $row[1];
                    }
                }


                // sources

                $results = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:source')
                );

                if(!empty($results->rows))
                {
                    foreach($results->rows as $row)
                    {
                        $data['sources'][$row[0]] = $row[1];
                    }
                }

                // countries

                $results = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:country')
                );

                if(!empty($results->rows))
                {
                    foreach($results->rows as $row)
                    {
                        $data['countries'][$row[0]] = $row[1];
                    }
                }
            }
            else
            {
                throw new Exception("Please select a web profile");
            }
        }
        catch(\Exception $e)
        {
            $error = $this->catchError($e);
            $this->returnErrorJson($error);
        }

        $this->returnJson($data);

    }

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

                if(craft()->config->get('disableCache', 'analytics') == true)
                {
                    $enableCache = false;
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

                        craft()->fileCache->set($cacheKey, $response, craft()->analytics->cacheExpiry());
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

                    //var_dump($options);
                }

                $this->returnJson(array('apiResponse' => $response));
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

    public function actionCustomReport(array $variables = array())
    {
        try {
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

            // setup options

            switch($widget->settings['options']['chartType'])
            {
                case 'ColumnChart':
                case 'PieChart':
                case 'Table':
                    $slices = (!empty($widget->settings['options']['slices']) ? $widget->settings['options']['slices'] : 2);

                    $options['sort'] = '-'.$widget->settings['options']['metric'];
                    $options['max-results'] = $slices;

                    break;

                case 'GeoChart':

                    $options['sort'] = '-'.$widget->settings['options']['metric'];

                    $continent = craft()->analytics->getContinentByCode($widget->settings['options']['region']);

                    $subContinent = craft()->analytics->getSubContinentByCode($widget->settings['options']['region']);

                    $country = craft()->analytics->getCountryByCode($widget->settings['options']['region']);

                    $options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                    $options['filters'] = $widget->settings['options']['dimension'].'!=(not set)';

                    if($continent)
                    {
                        //$options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                        $options['filters'] .= ';ga:continent=='.$continent['label'];
                    }
                    elseif ($subContinent)
                    {
                        //$options['filters'] = (!empty($options['filters']) ? $options['filters'].';' : '');
                        $options['filters'] .= ';ga:subContinent=='.$subContinent['label'];
                    }
                    elseif ($country)
                    {
                        $options['filters'] .= ';ga:country=='.$country['label'];
                    }

                    if($widget->settings['options']['chartType'] == 'GeoChart'
                        && $widget->settings['options']['dimension'] == 'ga:city')
                    {
                        $options['dimensions'] = "ga:latitude,ga:longitude,".$options['dimensions'];
                    }

                    break;
            }

            // request

            $enableCache = true;

            if(craft()->config->get('disableCache', 'analytics') == true)
            {
                $enableCache = false;
            }

            if($enableCache)
            {
                $cacheKey = 'analytics/customReport/'.md5(serialize(array(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                )));

                $response = craft()->fileCache->get($cacheKey);

                if(!$response)
                {
                    $response = craft()->analytics->apiGet(
                        'ga:'.$profile['id'],
                        $start,
                        $end,
                        $metric,
                        $options
                    );

                    craft()->fileCache->set($cacheKey, $response, craft()->analytics->cacheExpiry());
                }
            }
            else
            {
                $response = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    $options
                );
            }

            // response

            switch($widget->settings['options']['chartType'])
            {
                case 'GeoChart':

                if(isset($response['rows']))
                {
                    foreach($response['rows'] as $k => $row)
                    {
                        if(isset($row[0]))
                        {

                            $continent = craft()->analytics->getContinentByLabel($row[0]);
                            $subContinent = craft()->analytics->getSubContinentByLabel($row[0]);

                            if($continent)
                            {
                                $response['rows'][$k][0] = array(
                                    'v' => $continent['code'],
                                    'f' => $continent['label']
                                );
                            }
                            elseif($subContinent)
                            {
                                $response['rows'][$k][0] = array(
                                    'v' => $subContinent['code'],
                                    'f' => $subContinent['label']
                                );
                            }
                        }
                    }
                }

                break;
            }

            // json return

            $this->returnJson(array(
                'widget' => $widget,
                'apiResponse' => $response
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    private function catchError($e)
    {
        $errors = $e->getErrors();

        if(is_array($errors))
        {
            $error = $errors[0];
        }
        else
        {
            $error = $e->getMessage();
        }

        return $error;
    }
}