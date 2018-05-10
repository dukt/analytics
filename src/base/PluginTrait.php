<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\base;

use dukt\analytics\Plugin as Analytics;

/**
 * PluginTrait implements the common methods and properties for plugin classes.
 *
 * @property \dukt\analytics\services\Analytics                 $analytics                  The analytics service
 * @property \dukt\analytics\services\Apis                      $apis                       The apis service
 * @property \dukt\analytics\services\Cache                     $cache                      The cache service
 * @property \dukt\analytics\services\Metadata                  $metadata                   The metadata service
 * @property \dukt\analytics\services\Oauth                     $oauth                      The oauth service
 * @property \dukt\analytics\services\Reports                   $reports                    The reports service
 * @property \dukt\analytics\services\Views                     $views                    The views service
 */
trait PluginTrait
{
    /**
     * Returns the analytics service.
     *
     * @return \dukt\analytics\services\Analytics The analytics service
     * @throws \yii\base\InvalidConfigException
     */
    public function getAnalytics()
    {
        /** @var Analytics $this */
        return $this->get('analytics');
    }

    /**
     * Returns the apis service.
     *
     * @return \dukt\analytics\services\Apis The apis service
     * @throws \yii\base\InvalidConfigException
     */
    public function getApis()
    {
        /** @var Analytics $this */
        return $this->get('apis');
    }

    /**
     * Returns the cache service.
     *
     * @return \dukt\analytics\services\Cache The cache service
     * @throws \yii\base\InvalidConfigException
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
     * @throws \yii\base\InvalidConfigException
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
     * @throws \yii\base\InvalidConfigException
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
     * @throws \yii\base\InvalidConfigException
     */
    public function getReports()
    {
        /** @var Analytics $this */
        return $this->get('reports');
    }

    /**
     * Returns the views service.
     *
     * @return \dukt\analytics\services\Views The views service
     * @throws \yii\base\InvalidConfigException
     */
    public function getViews()
    {
        /** @var Analytics $this */
        return $this->get('views');
    }
}
