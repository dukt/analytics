<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_CacheService extends BaseApplicationComponent
{
    public function get($id)
    {
        $cacheKey = $this->getCacheKey($id);

        return craft()->cache->get($cacheKey);
    }

    public function set($id, $value, $expire = null, $dependency = null)
    {
        $cacheKey = $this->getCacheKey($id);

        return craft()->cache->set($cacheKey, $value, $expire, $dependency);
    }

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
