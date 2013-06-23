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
    public function event($category, $action, $label, $value)
    {
        return "
            ga('send', 'event', '".$category."', '".$action."', '".$label."', ".$value.");

            var el=this;

            setTimeout(function() {
                location.href = el.href;
            }, 100);

            return false;
            ";
    }

    public function getSetting($k)
    {
        return craft()->analytics->getSetting($k);
    }

    public function properties()
    {
        return craft()->analytics->properties();
    }

    public function code($id, $entry = NULL)
    {
        return craft()->analytics->code($id, $entry);
    }

    public function accounts()
    {
        return craft()->analytics->accounts();
    }

    public function api()
    {
        return craft()->analytics->api();
    }
}