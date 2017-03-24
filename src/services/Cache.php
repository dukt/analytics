<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use craft\helpers\DateTimeHelper;
use yii\base\Component;
use dukt\analytics\Plugin as Analytics;
use DateInterval;

class Cache extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get cache
     *
     * @param $id
     *
     * @return mixed
     */
    public function get($id)
    {
        if(Analytics::$plugin->getSettings()->enableCache == true)
        {
            $cacheKey = $this->getCacheKey($id);

            return Craft::$app->getCache()->get($cacheKey);
        }
    }

    /**
     * Set cache
     *
     * @param      $id
     * @param      $value
     * @param null $expire
     * @param null $dependency
     * @param null $enableCache
     *
     * @return mixed
     */
    public function set($id, $value, $expire = null, $dependency = null, $enableCache = null)
    {
        if(is_null($enableCache))
        {
            $enableCache = Analytics::$plugin->getSettings()->enableCache;
        }

        if($enableCache)
        {
            $cacheKey = $this->getCacheKey($id);

            if(!$expire)
            {
                $expire = $this->getCacheDuration();
            }

            return Craft::$app->getCache()->set($cacheKey, $value, $expire, $dependency);
        }
    }

    /**
     * Deletes a value with the specified key from cache.
     *
     * @param string $id The key of the value to be deleted.
     *
     * @return bool If no error happens during deletion.
     */
    public function delete($id)
    {
        $cacheKey = $this->getCacheKey($id);

        return Craft::$app->getCache()->delete($cacheKey);
    }

    // Private Methods
    // =========================================================================

    /**
     * Get cache duration (in seconds)
     *
     * @return int
     */
    private function getCacheDuration()
    {
        $duration = Analytics::$plugin->getSettings()->cacheDuration;

        return DateTimeHelper::intervalToSeconds(new DateInterval($duration));
    }

    /**
     * Get cache key
     *
     * @param array $request
     *
     * @return string
     */
    private function getCacheKey(array $request)
    {
        $dataSourceClassName = 'GoogleAnalytics';

        unset($request['CRAFT_CSRF_TOKEN']);

        $request[] = $dataSourceClassName;

        $hash = md5(serialize($request));

        $cacheKey = 'analytics.'.$hash;

        return $cacheKey;
    }
}
