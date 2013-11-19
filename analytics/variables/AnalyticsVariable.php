<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/craft/analytics/docs#license
 * @link      http://dukt.net/craft/analytics/
 */

namespace Craft;

class AnalyticsVariable
{
    public function getProfile()
    {
        return craft()->analytics->getProfile();
    }


    public function getWebProperty()
    {
        return craft()->analytics->getWebProperty();
    }

    public function isConfigured()
    {
        return craft()->analytics->isConfigured();
    }

    public function properties()
    {
        return craft()->analytics->properties();
    }

    public function realtime2()
    {
        $profile = $this->getProfile();

        $optParams = array('dimensions' => 'ga:pagePath');


        $results = craft()->analytics->api()->data_ga->get(
            'ga:'.$profile['id'],
            date('Y-m-d', strtotime('-1 week')),
            date("Y-m-d"),
            'ga:visits',
            array(
                'dimensions' => 'ga:day, ga:month, ga:year',
                'sort' => 'ga:year, ga:month, ga:day',
            )
        );

        var_dump($results);

    }
    public function realtime()
    {
        $profile = $this->getProfile();

        $results = craft()->analytics->api()->data_realtime->get(
            'ga:'.$profile['id'],
            'ga:activeVisitors',
            array('dimensions' => 'ga:pagePath')
        );

        var_dump($results);

    }
}