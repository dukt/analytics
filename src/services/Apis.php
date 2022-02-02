<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
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
