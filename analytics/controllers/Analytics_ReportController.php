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

class Analytics_ReportController extends BaseController
{
    private $widget;
    private $profile;
    private $start;
    private $end;

    public function init()
    {
        try {
            // widget
            $id = craft()->request->getParam('id');
            $this->widget = craft()->dashboard->getUserWidgetById($id);

            if($this->widget)
            {
                // profile
                $this->profile = craft()->analytics->getProfile();

                // start / end dates
                $this->start = date('Y-m-d', strtotime('-1 month'));
                $this->end = date('Y-m-d');


                $enableCache = true;

                if(craft()->config->get('disableCache', 'analytics') == true)
                {
                    $enableCache = false;
                }

                if($enableCache)
                {
                    $cacheKey = 'analytics/report/'.md5(serialize(array(
                        $this->profile,
                        $this->start,
                        $this->end,
                        $this->widget->settings['type']
                    )));

                    $reports = craft()->fileCache->get($cacheKey);

                    if(!$reports)
                    {
                        // call controller
                        $reports = $this->{$this->widget->settings['type']}();

                        craft()->fileCache->set($cacheKey, $reports, craft()->analytics->cacheExpiry());
                    }
                }
                else
                {
                    $reports = $this->{$this->widget->settings['type']}();
                }

                $this->returnJson(array(
                    'reports' => $reports
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

    public function acquisition()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:source',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:source!=(not set),ga:source!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:keyword',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:keyword!=(not set);ga:keyword!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:socialNetwork',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:socialNetwork!=(not set);ga:socialNetwork!=(not provided)'
                    )
                )
            )
        );

        return $reports;
    }

    public function mobile()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:isMobile',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:isMobile!=(not set),ga:isMobile!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:mobileDeviceInfo',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:mobileDeviceInfo!=(not set);ga:mobileDeviceInfo!=(not provided)'
                    )
                )
            )
        );

        return $reports;
    }

    public function pages()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:pagePath',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:pagePath!=(not set),ga:pagePath!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:landingPagePath',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:landingPagePath!=(not set);ga:landingPagePath!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:exitPagePath',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:exitPagePath!=(not set);ga:exitPagePath!=(not provided)'
                    )
                )
            )
        );

        return $reports;
    }
    public function technology()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:browser',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:browser!=(not set),ga:browser!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:operatingSystem',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:operatingSystem!=(not set);ga:operatingSystem!=(not provided)'
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:visits',
                    array(
                        'dimensions' => 'ga:screenResolution',
                        'sort' => '-ga:visits',
                        'max-results' => 10,
                        'filters' => 'ga:screenResolution!=(not set);ga:screenResolution!=(not provided)'
                    )
                )
            )
        );

        return $reports;
    }

    public function geo()
    {
        $reports = array(
            array(
                'options' => array('resolution' => 'countries', 'page' => 'enable'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:sessions',
                    array(
                        'dimensions' => 'ga:country',
                        'sort' => '-ga:sessions',
                    )
                )
            ),
            array(
                'options' => array('page' => 'enable'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:sessions',
                    array(
                        'dimensions' => 'ga:language',
                        'sort' => '-ga:sessions',
                        'filters' => 'ga:language!=(not set);ga:language!=(not provided)'
                    )
                )
            ),
            array(
                'options' => array('displayMode' => 'markers', 'page' => 'enable'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:sessions',
                    array(
                        'dimensions' => 'ga:city',
                        'sort' => '-ga:sessions',
                        'filters' => 'ga:city!=(not set);ga:city!=(not provided)'
                    )
                )
            )
        );

        return $reports;
    }

    public function conversions()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    $this->start,
                    $this->end,
                    'ga:goalCompletionsAll',
                    array(
                        'dimensions' => 'ga:date'
                    )
                )
            )
        );

        return $reports;
    }

    public function visits()
    {
        $reports = array(
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    date('Y-m-d', strtotime('-1 week')),
                    date('Y-m-d'),
                    'ga:sessions',
                    array(
                        'dimensions' => 'ga:day, ga:month, ga:year',
                        'sort' => 'ga:year, ga:month, ga:day',
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    date('Y-m-d', strtotime('-1 month')),
                    date('Y-m-d'),
                    'ga:sessions',
                    array(
                        'dimensions' => 'ga:day, ga:month, ga:year',
                        'sort' => 'ga:year, ga:month, ga:day',
                    )
                )
            ),
            array(
                //'options' => array('chartType' => 'PieChart'),
                'apiResponse' => craft()->analytics->apiGet(
                    'ga:'.$this->profile['id'],
                    date('Y-m-d', strtotime('-1 year')),
                    date('Y-m-d'),
                    'ga:sessions',
                    array(
                            'dimensions' => 'ga:month, ga:year',
                            'sort' => 'ga:year, ga:month',
                    )
                )
            ),
        );

        // post process rows

        foreach($reports as $k => $report)
        {

            $newCols = array();
            $newRows = array();

            if(count($report['apiResponse']['cols']) > 2)
            {
                $col1 = $report['apiResponse']['cols'][0];
                $col2 = end($report['apiResponse']['cols']);
            }

            foreach($report['apiResponse']['rows'] as $v) {
                $itemMetric = (int) array_pop($v);
                $itemDimension = implode('.', $v);

                $item = array(
                    $col1['name'] => $itemDimension,
                    $col2['name'] => $itemMetric,
                );
                array_push($newRows, $item);
            }

            $newCols = array($col1, $col2);

            $reports[$k]['apiResponse']['cols'] = $newCols;
            $reports[$k]['apiResponse']['rows'] = $newRows;
        }

        return $reports;
    }
}