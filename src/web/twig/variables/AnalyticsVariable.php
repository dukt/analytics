<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\web\twig\variables;

use dukt\analytics\models\ReportRequestCriteria;
use dukt\analytics\etc\craft\AnalyticsTracking;
use dukt\analytics\Plugin as Analytics;

class AnalyticsVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a ReportRequestCriteria model that can be sent to request Google Analytics' API.
     *
     * @param array $attributes
     *
     * @return ReportRequestCriteria
     */
    public function api($attributes = null)
    {
        return new ReportRequestCriteria($attributes);
    }

    /**
     * Sends tracking data to Google Analytics.
     *
     * @param array $options
     *
     * @return AnalyticsTracking|null
     */
    public function track($options = null)
    {
        return Analytics::$plugin->getAnalytics()->track($options);
    }

    /**
     * Returns the Analytics tracking object.
     *
     * @param bool $isSsl
     * @param bool $isDisabled
     * @param array $options
     * @throws \InvalidArgumentException
     *
     * @return \TheIconic\Tracking\GoogleAnalytics\Analytics
     */
    public function tracking($isSsl = false, $isDisabled = false, array $options = [])
    {
        return Analytics::$plugin->getAnalytics()->tracking($isSsl, $isDisabled, $options);
    }
}
