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
    public function getAccount()
    {
        return craft()->analytics->getAccount();
    }

    public function api()
    {
        return craft()->analytics->api();
    }
}