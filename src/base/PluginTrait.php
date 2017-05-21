<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\base;

use dukt\analytics\Plugin as Analytics;

/**
 * PluginTrait implements the common methods and properties for plugin classes.
 *
 * @property \dukt\analytics\services\Analytics                 $analytics                  The analytics service
 * @property \dukt\analytics\services\AnalyticsReportingApi     $analyticsReportingApi      The Analytics reporting api service
 * @property \dukt\analytics\services\AnalyticsApi              $analyticsApi               The api service
 * @property \dukt\analytics\services\Cache                     $cache                      The cache service
 * @property \dukt\analytics\services\Metadata                  $metadata                   The metadata service
 * @property \dukt\analytics\services\Oauth                     $oauth                      The oauth service
 * @property \dukt\analytics\services\Reports                   $reports                    The reports service
 */
trait PluginTrait
{
    /**
     * Returns the analytics service.
     *
     * @return \dukt\analytics\services\Analytics The analytics service
     */
    public function getAnalytics()
    {
        /** @var Analytics $this */
        return $this->get('analytics');
    }

    /**
     * Returns the api service.
     *
     * @return \dukt\analytics\services\AnalyticsApi The api service
     */
    public function getAnalyticsApi()
    {
        /** @var Analytics $this */
        return $this->get('analyticsApi');
    }

    /**
     * Returns the api service.
     *
     * @return \dukt\analytics\services\AnalyticsReportingApi The api service
     */
    public function getAnalyticsReportingApi()
    {
        /** @var Analytics $this */
        return $this->get('analyticsReportingApi');
    }

    /**
     * Returns the cache service.
     *
     * @return \dukt\analytics\services\Cache The cache service
     */
    public function getCache()
    {
        /** @var Analytics $this */
        return $this->get('cache');
    }

    /**
     * Returns the metadata service.
     *
     * @return \dukt\analytics\services\Metadata The metadata service
     */
    public function getMetadata()
    {
        /** @var Analytics $this */
        return $this->get('metadata');
    }

    /**
     * Returns the oauth service.
     *
     * @return \dukt\analytics\services\Oauth The oauth service
     */
    public function getOauth()
    {
        /** @var Analytics $this */
        return $this->get('oauth');
    }

    /**
     * Returns the reports service.
     *
     * @return \dukt\analytics\services\Reports The reports service
     */
    public function getReports()
    {
        /** @var Analytics $this */
        return $this->get('reports');
    }
}
