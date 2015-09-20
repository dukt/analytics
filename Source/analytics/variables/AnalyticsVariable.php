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
     * Track
     */
    public function track($options = null)
    {
        return craft()->analytics->track($options);
    }

    /**
     * Request the API.
     *
     * @param array $attributes
     *
     * @return Analytics_RequestCriteriaModel
     */
    public function api($attributes = null)
    {
        return new Analytics_RequestCriteriaModel($attributes);
    }
}
