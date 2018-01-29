<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use craft\base\Component;

class Apis extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @return \dukt\analytics\apis\Analytics
     */
    public function getAnalytics()
    {
        return new \dukt\analytics\apis\Analytics();
    }

    /**
     * @return \dukt\analytics\apis\AnalyticsReporting
     */
    public function getAnalyticsReporting()
    {
        return new \dukt\analytics\apis\AnalyticsReporting();
    }
}
