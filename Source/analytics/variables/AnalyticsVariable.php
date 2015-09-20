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

    public function track($options = null)
    {
        return craft()->analytics->track($options);
    }

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
            return craft()->analytics_oauth->getToken();
        }
        catch(\Exception $e)
        {
            Craft::log('Couldn’t get token: '.$e->getMessage(), LogLevel::Error);
        }
    }
    }
}
