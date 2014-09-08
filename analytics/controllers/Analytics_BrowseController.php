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
    public function actionCombined()
    {
        try {

            // parameter

            $id = craft()->request->getParam('id');
            $dimension = craft()->request->getParam('dimension');
            $metric = craft()->request->getParam('metric');
            $period = craft()->request->getParam('period');

            // widget
            $widget = craft()->dashboard->getUserWidgetById($id);

            // profile
            $profile = craft()->analytics->getProfile();

            // start / end dates
            $start = date('Y-m-d', strtotime('-1 '.$period));
            $end = date('Y-m-d');



            // chart


            switch($period)
            {
                case 'year':
                $chartDimension = 'ga:yearMonth';
                break;

                default:
                $chartDimension = 'ga:date';
            }

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

            $chartResponse = $this->localizeApiResponse($chartResponse);
            $chartRows = $this->parseRows($chartResponse);
            $chart = array(
                'columns' => $chartResponse['cols'],
                'rows' => $chartRows,
            );


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


            // table

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

            $tableResponse = $this->localizeApiResponse($tableResponse);

            $table = array(
                'columns' => $tableResponse['cols'],
                'rows' => $this->parseRows($tableResponse)
            );


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

                //$rows = $response['rows'];
                $rows = $this->parseRows($response);

                // return
                $this->returnJson(array(
                    'columns' => $response['cols'],
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

                $response = $this->localizeApiResponse($response);

                $rows = $this->parseRows($response);

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

    public function localizeApiResponse($response)
    {
        // cols
        foreach($response['cols'] as $key => $col)
        {
            $response['cols'][$key]->label = Craft::t($col->name);
        }

        return $response;
    }

    public function parseRows($apiResponse)
    {
        $cols = $apiResponse['cols'];

        $rows = array();

        foreach($apiResponse['rows'] as $apiRow)
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

                switch($key)
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

        return $rows;
    }
}