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
        $widgetId = craft()->request->getPost('id');

        $widgetSettings = array();
        $widgetSettings['menu'] = craft()->request->getPost('menu');
        $widgetSettings['dimension'] = craft()->request->getPost('dimension');
        $widgetSettings['metric'] = craft()->request->getPost('metric');
        $widgetSettings['chart'] = craft()->request->getPost('chart');
        $widgetSettings['period'] = craft()->request->getPost('period');
        $widgetSettings['pinned'] = (bool) craft()->request->getPost('pinned');

        $formerWidget = craft()->dashboard->getUserWidgetById($widgetId);

        if($formerWidget)
        {
            $widgetSettings['colspan'] = $formerWidget->settings['colspan'];
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

    public function actionTable()
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

            foreach($table['rows'] as $k => $row)
            {
                $table['rows'][$k][0]['f'] = Craft::t($table['rows'][$k][0]['f']);
            }

            $this->returnJson(array(
                'table' => $table,
                'dimension' => Craft::t($dimension),
                'metric' => Craft::t($metric),
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
        $this->actionTable();
    }

    public function actionArea()
    {
        try
        {
            $realtime = craft()->request->getParam('realtime');
            $profile = craft()->analytics->getProfile();
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

            $total = 0;

            foreach($rows as $row)
            {
                $total += $row[1]['v'];
            }

            $area = array(
                'columns' => $cols,
                'rows' => $rows,
            );

            $this->returnJson(array(
                'area' => $area,
                'total' => $total,
                'metric' => Craft::t($metric),
                'period' => Craft::t('this '.$period)
            ));
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
            $metric = craft()->request->getParam('metric');
            $period = craft()->request->getParam('period');
            $start = date('Y-m-d', strtotime('-1 '.$period));
            $end = date('Y-m-d');

            if($realtime)
            {
                $chartResponse = craft()->analytics->apiRealtimeGet(
                    'ga:'.$profile['id'],
                    $metric,
                    array()
                );

                $cols = $this->localizeApiResponse($chartResponse['columnHeaders']);
                $rows = $chartResponse->rows;

                $counter = array(
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

                $counter = array(
                    'count' => $this->formatValue($totalApiResponse['cols'][0]->dataType, $totalApiResponse['rows'][0][$metric])
                );
            }

            $this->returnJson(array(
                'counter' => $counter,
                'metric' => Craft::t($metric),
                'period' => Craft::t('this '.$period)
            ));
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
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

    private function formatRawValue($type, $value)
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

    private function formatValue($type, $value)
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

    private function localizeApiResponse($cols)
    {
        foreach($cols as $key => $col)
        {
            $cols[$key]->label = Craft::t($col->name);
        }

        return $cols;
    }

    private function parseRows($cols, $apiRows = null)
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

                        $cell = strftime("%Y.%m.%d", strtotime($value));

                        // $cell = array(
                        //     'v' => $value,
                        //     'f' => strftime("%Y.%m.%d", strtotime($value))
                        // );

                        break;

                        case 'ga:yearMonth':
                        $cell = strftime("%Y.%m.%d", strtotime($value.'01'));
                        //$cell = strftime("%Y.%m.%d", strtotime($value.'01'));
                        // $datetime = new DateTime('@'.strtotime($value.'01'));

                        // $cell = array(
                        //     'v' => strftime("%Y.%m.%d", strtotime($value.'01')),
                        //     'f' => $datetime->format("F y"),
                        // );
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