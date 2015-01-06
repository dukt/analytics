<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

class AnalyticsVariable
{
    public function api($options)
    {
        return craft()->analytics->api($options);
    }

    public function getToken()
    {
        try
        {
            return craft()->analytics->getToken();
        }
        catch(\Exception $e)
        {
            Craft::log('Couldn’t get token: '.$e->getMessage(), LogLevel::Info, true);
        }
    }

    public function getWebProperty()
    {
        return craft()->analytics->getWebProperty();
    }

    public function getProfile()
    {
        return craft()->analytics->getProfile();
    }

    public function isConfigured()
    {
        return craft()->analytics->isConfigured();
    }
}