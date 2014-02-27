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

class Analytics_ChartsController extends BaseController
{
    private function cacheExpiry()
    {
        $cacheExpiry = craft()->config->get('analyticsCacheExpiry');

        if(!$cacheExpiry)
        {
            $cacheExpiry = 30 * 60; // 30 min cache
        }

        return $cacheExpiry;
    }
    public function actionGetChart()
    {
        try {

            $data = craft()->request->getParam('data');

            $cacheKey = 'analytics/getCharts/'.md5(serialize($data));

            $chart = craft()->fileCache->get($cacheKey);

            if(!$chart)
            {
                $chart = craft()->analytics->getChartFromData($data);

                craft()->fileCache->set($cacheKey, $chart, $this->cacheExpiry());
            }

            $this->returnJson(array('chart' => $chart));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
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
                $response = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    'ga:visits, ga:entrances, ga:exits, ga:pageviews, ga:timeOnPage, ga:exitRate, ga:entranceRate, ga:pageviewsPerVisit, ga:avgTimeOnPage, ga:visitBounceRate'
                );

                craft()->fileCache->set($cacheKey, $response, $this->cacheExpiry());
            }

            $rows = $this->getRows($response);

            $row = array_pop($rows);

            $counts = array(
                'visits'         => $row['ga:visits'],
                'entrances'         => $row['ga:entrances'],
                'exits'             => $row['ga:exits'],
                'pageviews'         => $row['ga:pageviews'],
                'timeOnPage'        => $row['ga:timeOnPage'],
                'exitRate'   => $row['ga:exitRate'],
                'entranceRate'      => $row['ga:entranceRate'],
                'pageviewsPerVisit' => $row['ga:pageviewsPerVisit'],
                'avgTimeOnPage'     => $row['ga:avgTimeOnPage'],
                'visitBounceRate'          => $row['ga:visitBounceRate'],
            );

            $html = craft()->templates->render('analytics/_includes/reports/counts', array(
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

                $results = craft()->analytics->api()->data_realtime->get(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:visitorType')
                );


                if(!empty($results['totalResults']))
                {
                    $data['total'] = $results['totalResults'];
                }

                if(!empty($results['rows'][0][1]))
                {
                    switch($results['rows'][0][0])
                    {
                        case "RETURNING":
                        $data['visitorType']['returningVisitor'] = $results['rows'][0][1];
                        break;

                        case "NEW":
                        $data['visitorType']['newVisitor'] = $results['rows'][0][1];
                        break;
                    }
                }

                if(!empty($results['rows'][1][1]))
                {
                    switch($results['rows'][1][0])
                    {
                        case "RETURNING":
                        $data['visitorType']['returningVisitor'] = $results['rows'][1][1];
                        break;

                        case "NEW":
                        $data['visitorType']['newVisitor'] = $results['rows'][1][1];
                        break;
                    }
                }


                // content

                $results = craft()->analytics->api()->data_realtime->get(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:pagePath')
                );

                if(!empty($results['rows']))
                {
                    foreach($results['rows'] as $row)
                    {
                        $data['content'][$row[0]] = $row[1];
                    }
                }


                // sources

                $results = craft()->analytics->api()->data_realtime->get(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:source')
                );

                if(!empty($results['rows']))
                {
                    foreach($results['rows'] as $row)
                    {
                        $data['sources'][$row[0]] = $row[1];
                    }
                }

                // countries

                $results = craft()->analytics->api()->data_realtime->get(
                    'ga:'.$profile['id'],
                    'ga:activeVisitors',
                    array('dimensions' => 'ga:country')
                );

                if(!empty($results['rows']))
                {
                    foreach($results['rows'] as $row)
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

    public function actionParse()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        try {
            $chartQuery = $_POST['chartQuery'];

            $results = craft()->analytics->api()->data_ga->get(
                $chartQuery['param1'],
                $chartQuery['param2'],
                $chartQuery['param3'],
                $chartQuery['param4'],
                $chartQuery['param5']
            );

            $json = array(
                    array(
                        'Column 1',
                        'Column 2'
                    )
                );

            foreach($results['rows'] as $row)
            {
                $itemMetric = (int) array_pop($row);
                $itemDimension = implode('.', $row);
                // $itemDimension = md5($itemDimension);

                if($itemDimension != "(not provided)" && $itemDimension != "(not set)")
                {
                    $item = array($itemDimension, $itemMetric);
                    array_push($json, $item);
                }
            }

            $variables = array('json' => $json);

            $this->returnJson($json);

        }
        catch(\Exception $e)
        {
            $error = $this->catchError($e);
            $this->returnErrorJson($error);
        }
    }

    public function actionParseTable()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        try {
            $cols = $_POST['cols'];
            $chartQuery = $_POST['chartQuery'];

            $results = craft()->analytics->api()->data_ga->get(
                $chartQuery['param1'],
                $chartQuery['param2'],
                $chartQuery['param3'],
                $chartQuery['param4'],
                $chartQuery['param5']
            );

            $json = array(
                'cols' => $cols,
                'rows' => array()
            );

            foreach($results['rows'] as $row)
            {
                $itemMetric = (int) array_pop($row);
                $itemDimension = implode('.', $row);
                // $itemDimension = md5($itemDimension);

                if($itemDimension != "(not provided)" && $itemDimension != "(not set)")
                {
                    $item = array(
                        'c' => array(
                            array('v' => '<strong>'.$itemDimension.'</strong>', 'p' => array('style' => 'border:0; border-bottom: 1px dotted #e3e5e8;')),
                            array('v' => $itemMetric, 'p' => array('style' => 'width:30%; border:0; border-bottom: 1px dotted #e3e5e8;')),
                        )
                    );

                    array_push($json['rows'], $item);
                }
            }

            $variables = array('json' => $json);


            $this->returnJson($json);

        } catch(\Exception $e)
        {

            $errors = $e->getErrors();

            if(is_array($errors))
            {
                $errors = $errors[0];
            }
            else
            {
                $errors = $e->getMessage();
            }

            $this->returnErrorJson($errors);
        }
    }


    private function getRows($response)
    {
        $columns = $response['columnHeaders'];

        $rows = $response['rows'];
        $newRows = array();

        foreach($rows as $row)
        {
            $newRow = array();

            foreach($row as $k => $v)
            {
                $newRow[$columns[$k]['name']] = $this->formatCell($v, $columns[$k]);
            }

            array_push($newRows, $newRow);

        }

        return $newRows;
    }

    private function secondMinute($seconds)
    {
        $minResult = floor($seconds/60);

        if($minResult < 10)
        {
            $minResult = 0 . $minResult;
        }

        $secResult = ($seconds/60 - $minResult) * 60;

        if(round($secResult) < 10){
            $secResult = 0 . round($secResult);
        }
        else
        {
            $secResult = round($secResult);
        }

        return $minResult.":".$secResult;
    }

    private function formatCell($value, $column)
    {
        switch($column['name'])
        {
            case "ga:avgTimeOnPage":
                $value = $this->secondMinute($value);
                return $value;
                break;

            case 'ga:pageviewsPerVisit':

                $value = round($value, 2);
                return $value;

                break;

            case 'ga:entranceRate':
            case 'ga:visitBounceRate':
            case 'ga:exitRate':

                $value = round($value, 2)."%";
                return $value;

                break;

            default:
                return $value;
        }
    }
}