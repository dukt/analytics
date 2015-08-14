<?php

namespace Dukt\Analytics\DataSources;

use Craft\Craft;

class Craft extends BaseDataSource
{
    public function table()
    {
        $period = craft()->request->getParam('period');
        $dimension = craft()->request->getParam('dimension'); // sections or elementTypes


        switch($dimension)
        {
            case 'sections':
            $sections = craft()->sections->getAllSections();

            $chartResponse = array(
                'cols' => array(
                    array(
                        'dataType' => "STRING",
                        'id' => 'section',
                        'label' => "Section",
                        'type' => "string",
                    ),
                    array(
                        'dataType' => "INTEGER",
                        'id' => "entries",
                        'label' => "Entries",
                        'type' => "number",
                    ),
                ),
                'rows' => array()
            );

            foreach($sections as $section)
            {
                $criteria = craft()->elements->getCriteria(ElementType::Entry);
                $criteria->sectionId = $section->id;
                $entries = $criteria->find();

                $chartResponse['rows'][] = [
                    ['v' => $section->name, 'f' => $section->name],
                    ['v' => count($entries), 'f' => (string) count($entries)],
                ];
            }

            break;

            case 'elementTypes':

            $criteria = craft()->elements->getCriteria(ElementType::Entry);
            $entries = $criteria->find();

            $criteria = craft()->elements->getCriteria(ElementType::Asset);
            $assets = $criteria->find();

            $criteria = craft()->elements->getCriteria(ElementType::User);
            $users = $criteria->find();


            $chartResponse = array(
                'cols' => array(
                    array(
                        'dataType' => "STRING",
                        'id' => 'elementType',
                        'label' => "ElementType",
                        'type' => "string",
                    ),
                    array(
                        'dataType' => "INTEGER",
                        'id' => "elements",
                        'label' => "Elements",
                        'type' => "number",
                    ),
                ),
                'rows' => array()
            );

            $chartResponse['rows'][] = [
                ['v' => "Entries", 'f' => "Entries"],
                ['v' => count($entries), 'f' => (string) count($entries)],
            ];

            $chartResponse['rows'][] = [
                ['v' => "Assets", 'f' => "Assets"],
                ['v' => count($assets), 'f' => (string) count($assets)],
            ];

            $chartResponse['rows'][] = [
                ['v' => "Users", 'f' => "Users"],
                ['v' => count($users), 'f' => (string) count($users)],
            ];

            break;
        }

        return [
            'table' => $chartResponse,
            'total' => 0,
            'dimension' => 'ga:users',
            'metric' => 'ga:users',
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
    public function area()
    {
        $period = craft()->request->getParam('period');
        $element = craft()->request->getParam('element');
        $sectionId = craft()->request->getParam('section');
        $groupId = craft()->request->getParam('group');

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

                if($sectionId)
                {
                    $criteria->sectionId = $sectionId;
                }

                $criteria->order = 'postDate DESC';

                break;

            case ElementType::User:
                $criteria->order = 'dateCreated DESC';
                break;
        }

        $elements = $criteria->find();

        if($groupId)
        {
            switch ($elementType)
            {
                case ElementType::User:
                    foreach($elements as $key => $el)
                    {
                        if(!$el->isInGroup($groupId))
                        {
                            // unset user as it doesn't belong to the selected group
                            unset($elements[$key]);
                        }
                    }
                    break;
            }
        }

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
