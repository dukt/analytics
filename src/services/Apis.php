<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use craft\base\Component;

class Apis extends Component
{
    public function getAnalytics()
    {
        return new \dukt\analytics\apis\Analytics();
    }

    public function getAnalyticsReporting()
    {
        return new \dukt\analytics\apis\AnalyticsReporting();
    }
}
