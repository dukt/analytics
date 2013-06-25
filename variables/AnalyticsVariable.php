<?php

/**
 * Craft Analytics
 *
 * @package     Craft Analytics
 * @version     Version 1.0
 * @author      Benjamin David
 * @copyright   Copyright (c) 2013 - DUKT
 * @link        http://dukt.net/add-ons/craft/analytics/
 *
 */

namespace Craft;

class AnalyticsVariable
{
    // --------------------------------------------------------------------

    public function isInstalled()
    {
        return craft()->analytics->isInstalled();
    }

    // --------------------------------------------------------------------

    public function isConfigured()
    {
        return craft()->analytics->isConfigured();
    }

    // --------------------------------------------------------------------

    public function checkUpdates($pluginClass, $pluginHandle)
    {
        return craft()->analytics->checkUpdates($pluginClass, $pluginHandle);
    }


    // --------------------------------------------------------------------

    public function trackEvent($category, $action=null, $label=null, $value=0)
    {
        return craft()->analytics->trackEvent($category, $action, $label, $value);
    }

    // --------------------------------------------------------------------

    public function getSetting($k)
    {
        return craft()->analytics->getSetting($k);
    }

    // --------------------------------------------------------------------

    public function properties()
    {
        return craft()->analytics->properties();
    }

    // --------------------------------------------------------------------

    public function code()
    {
        return craft()->analytics->code();
    }

    // --------------------------------------------------------------------

    public function api()
    {
            return craft()->analytics->api();
    }
}