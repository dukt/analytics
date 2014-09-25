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

class Analytics_BrowseController extends BaseController
{
    public function actionSaveState()
    {
        $widgetSettings = array();
        $widgetSettings['menu'] = craft()->request->getPost('menu');
        $widgetSettings['dimension'] = craft()->request->getPost('dimension');
        $widgetSettings['metric'] = craft()->request->getPost('metric');
        $widgetSettings['chart'] = craft()->request->getPost('chart');
        $widgetSettings['period'] = craft()->request->getPost('period');
        $widgetSettings['pinned'] = (bool) craft()->request->getPost('pinned');

        $widget = new WidgetModel();
        $widget->id = craft()->request->getPost('id');
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


        // $this->returnErrorJson('Widget not found');
    }

    public function actionCombined()
    {
        try {

            // parameter
            $id = craft()->request->getParam('id');
            $dimension = craft()->request->getParam('dimension');
            $metric = craft()->request->getParam('metric');
            $period = craft()->request->getParam('period');
            $realtime = craft()->request->getParam('realtime');

            // widget
            $widget = craft()->dashboard->getUserWidgetById($id);

            // profile
            $profile = craft()->analytics->getProfile();

            // start / end dates
            $start = date('Y-m-d', strtotime('-1 '.$period));
            $end = date('Y-m-d');


            // chart
            $chart = $this->chart($realtime, $profile, $start, $end, $metric, $dimension, $period);

            // total
            $total = $this->total($realtime, $profile, $start, $end, $metric, $dimension);

            // table
            $table = $this->table($realtime, $profile, $start, $end, $metric, $dimension);

            // return json
            $this->returnJson(array(
                'chart' => $chart,
                'table' => $table,
                'total' => $total
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    private function chart($realtime, $profile, $start, $end, $metric, $dimension, $period)
    {
        switch($period)
        {
            case 'year':
            $chartDimension = 'ga:yearMonth';
            break;

            default:
            $chartDimension = 'ga:date';
        }


        if($realtime)
        {
            $chartResponse = craft()->analytics->apiRealtimeGet(
                'ga:'.$profile['id'],
                $metric,
                array('dimensions' => 'rt:userType')
            );

            $cols = $chartResponse['columnHeaders'];
            $rows = $chartResponse->rows;
        }
        else
        {
            $chartResponse = craft()->analytics->apiGet(
                'ga:'.$profile['id'],
                $start,
                $end,
                $metric,
                array(
                    'dimensions' => $chartDimension,
                    'sort' => $chartDimension,
                )
            );

            $cols = $chartResponse['cols'];
            $rows = $chartResponse['rows'];
        }


        $cols = $this->localizeApiResponse($cols);
        $rows = $this->parseRows($cols, $rows);

        $chart = array(
            'columns' => $cols,
            'rows' => $rows,
        );

        return $chart;
    }

    private function total($realtime, $profile, $start, $end, $metric)
    {
        if($realtime)
        {
            $chartResponse = craft()->analytics->apiRealtimeGet(
                'ga:'.$profile['id'],
                $metric,
                array()
            );

            $cols = $this->localizeApiResponse($chartResponse['columnHeaders']);
            $rows = $chartResponse->rows;

            $total = array(
                'count' => $this->formatValue($cols[0]->dataType, $rows[0][0]),
                'label' => strtolower(Craft::t($metric))
            );
        }
        else
        {
            $totalApiResponse = craft()->analytics->apiGet(
                'ga:'.$profile['id'],
                $start,
                $end,
                $metric
            );

            $total = array(
                'count' => $this->formatValue($totalApiResponse['cols'][0]->dataType, $totalApiResponse['rows'][0][$metric]),
                'label' => strtolower(Craft::t($metric))
            );
        }

        return $total;
    }

    private function table($realtime, $profile, $start, $end, $metric, $dimension)
    {
        if($realtime)
        {
            $table = $this->_realtimeRequest($profile, $metric, $dimension);
        }
        else
        {
            $table = $this->_request($profile, $start, $end, $metric, $dimension, array(
                    'sort' => '-'.$metric,
                    'max-results' => 20,
                ));
        }

        return $table;
    }

    private function _request($profile, $start, $end, $metrics, $dimensions = null, $params = array())
    {
        if($dimensions)
        {
            $params['dimensions'] = $dimensions;
        }

        $tableResponse = craft()->analytics->apiGet(
            'ga:'.$profile['id'],
            $start,
            $end,
            $metrics,
            $params
        );

        $cols = $tableResponse['cols'];
        $rows = $tableResponse['rows'];

        $cols = $this->localizeApiResponse($cols);
        $rows = $this->parseRows($cols, $rows);

        return array(
            'columns' => $cols,
            'rows' => $rows
        );
    }

    private function _realtimeRequest($profile, $metric, $dimensions)
    {
        $chartResponse = craft()->analytics->apiRealtimeGet(
            'ga:'.$profile['id'],
            $metric,
            array('dimensions' => $dimensions)
        );

        $cols = $chartResponse['columnHeaders'];
        $rows = $chartResponse->rows;
        $newRows = array();

        if($rows)
        {
            foreach($rows as $row)
            {
                $newRow = array();

                $i = 0;
                foreach($cols as $k => $col)
                {
                    $rowItem = $row[$i];
                    $newRow[$k] = $rowItem;
                    $i++;
                }

                array_push($newRows, $newRow);
            }
        }

        $rows = $newRows;

        $cols = $this->localizeApiResponse($cols);

        $rows = $this->parseRows($cols, $rows);

        return array(
                'columns' => $cols,
                'rows' => $rows
            );
    }

    public function actionTable()
    {
        try {

            // widget

            $id = craft()->request->getParam('id');
            $dimension = craft()->request->getParam('dimension');
            $metric = craft()->request->getParam('metric');
            $period = craft()->request->getParam('period');

            $widget = craft()->dashboard->getUserWidgetById($id);

            if($widget)
            {
                // profile
                $profile = craft()->analytics->getProfile();

                // start / end dates
                $start = date('Y-m-d', strtotime('-1 '.$period));
                $end = date('Y-m-d');

                // api response

                $response = craft()->analytics->apiGet(
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

                $response = $this->localizeApiResponse($response);

                $cols = $response['cols'];
                $rows = $this->parseRows($cols, $response['rows']);

                // return
                $this->returnJson(array(
                    'columns' => $cols,
                    'rows' => $rows
                ));
            }
            else
            {
                $this->returnErrorJson('Widget not found');
            }
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    public function actionChart()
    {
        try {

            // widget

            $id = craft()->request->getParam('id');
            $dimension = craft()->request->getParam('dimension');
            $metric = craft()->request->getParam('metric');
            $period = craft()->request->getParam('period');

            $widget = craft()->dashboard->getUserWidgetById($id);

            if($widget)
            {
                // profile
                $profile = craft()->analytics->getProfile();

                // start / end dates
                $start = date('Y-m-d', strtotime('-1 '.$period));
                $end = date('Y-m-d');

                // response

                switch($period)
                {
                    case 'year':
                    $dimension = 'ga:yearMonth';
                    break;

                    default:
                    $dimension = 'ga:date';
                }

                // response
                $response = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric,
                    array(
                        'dimensions' => $dimension,
                        'sort' => $dimension,
                    )
                );

                $cols = $this->localizeApiResponse($response['cols']);
                $rows = $this->parseRows($cols, $response['rows']);

                // total
                $totalApiResponse = craft()->analytics->apiGet(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metric
                );

                $total = array(
                    'count' => $this->formatValue($totalApiResponse['cols'][0]->dataType, $totalApiResponse['rows'][0][$metric]),
                    'label' => strtolower(Craft::t($metric))
                );

                // return
                $this->returnJson(array(
                    'columns' => $response['cols'],
                    'rows' => $rows,
                    'total' => $total
                ));
            }
            else
            {
                $this->returnErrorJson('Widget not found');
            }
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    public function formatRawValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'FLOAT':
            case 'TIME':
            case 'PERCENT':
            $value = (float) $value;
            break;

            default:
            $value = (string) $value;
        }

        return $value;
    }

    public function formatValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'FLOAT':
            case 'TIME':
            $value = (float) $value;
            $value = round($value, 2);
            break;

            case 'PERCENT':
            $value = (float) $value;
            $value = round($value, 2);
            $value = $value.'%';

            break;

            default:
            $value = (string) $value;
        }

        return $value;
    }

    public function localizeApiResponse($cols)
    {
        // cols
        foreach($cols as $key => $col)
        {
            $cols[$key]->label = Craft::t($col->name);
        }

        return $cols;
    }

    public function parseRows($cols, $apiRows = null)
    {
        $rows = array();
        if($apiRows)
        {

            foreach($apiRows as $apiRow)
            {
                $row = array();

                $colNumber = 0;

                foreach($apiRow as $key => $value)
                {
                    $col = $cols[$colNumber];
                    $value = $this->formatRawValue($col->dataType, $value);



                    $cell = array(
                        'v' => $value,
                        'f' => (string) $this->formatValue($col->dataType, $value)
                    );

                    switch($col->name)
                    {
                        case 'ga:date':

                        $cell = array(
                            'v' => $value,
                            'f' => strftime("%Y.%m.%d", strtotime($value))
                        );

                        break;

                        case 'ga:yearMonth':

                        $cell = array(
                            'v' => $value,
                            'f' => strftime("%Y.%m", strtotime($value.'01'))
                        );

                        break;
                    }

                    array_push($row, $cell);

                    $colNumber++;
                }

                array_push($rows, $row);
            }

        }
        return $rows;
    }
}