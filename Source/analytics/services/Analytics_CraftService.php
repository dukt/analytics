<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_CraftService extends BaseApplicationComponent
{
    public function area()
    {
        $period = craft()->request->getParam('period');
        $element = craft()->request->getParam('element');

        $metric = '';

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

        return [
            'area' => $chartResponse,
            'total' => $total,
            'metric' => 'ga:users',
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
}
