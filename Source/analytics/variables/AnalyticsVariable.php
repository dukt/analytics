<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Request the API.
     */
    public function api($attributes = null)
    {
        return new Analytics_RequestCriteriaModel($attributes);
    }

    /**
     * Get Token
     */
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

    /**
     * Get Web Property
     */
    public function getWebProperty()
    {
        return craft()->analytics->getWebProperty();
    }

    /**
     * Get Profile
     */
    public function getProfile()
    {
        try
        {
            return craft()->analytics->getProfile();
        }
        catch(\Exception $e)
        {
            Craft::log('Couldn’t get profile: '.$e->getMessage(), LogLevel::Info, true);
        }
    }

    /**
     * Is Configured
     */
    public function isConfigured()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        return $plugin->isConfigured();
    }
}