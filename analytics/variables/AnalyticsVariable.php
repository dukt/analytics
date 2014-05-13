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

class AnalyticsVariable
{
    public function getMetricOpts($params = array())
    {
        return craft()->analytics->getMetricOpts($params);
    }

    public function getDimensionOpts($params = array())
    {
        return craft()->analytics->getDimensionOpts($params);
    }

    public function getGeoRegionOpts($params)
    {
        return craft()->analytics->getGeoRegionOpts($params);
    }

    public function getProfile()
    {
        try
        {
            return craft()->analytics->getProfile();
        }
        catch(\Exception $e)
        {
            $r['error'] = $e->getMessage();
        }
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

    public function getAccount()
    {
        return craft()->analytics->getAccount();
    }

    public function api($options)
    {
        return craft()->analytics->api($options);
    }
}