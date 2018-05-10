<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\web\twig\variables;

use dukt\analytics\models\ReportRequestCriteria;
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
     * Returns the Analytics tracking object.
     *
     * @param bool  $isSsl
     * @param bool  $isDisabled
     * @param array $options
     *
     * @return \TheIconic\Tracking\GoogleAnalytics\Analytics
     * @throws \yii\base\InvalidConfigException
     */
    public function tracking($isSsl = false, $isDisabled = false, array $options = [])
    {
        return Analytics::$plugin->getAnalytics()->tracking($isSsl, $isDisabled, $options);
    }
}
