<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_CacheService extends BaseApplicationComponent
{
    public function get($id)
    {
        if(craft()->config->get('enableCache', 'analytics') == true)
        {
            $cacheKey = $this->getCacheKey($id);

            return craft()->cache->get($cacheKey);
        }
    }

    public function set($id, $value, $expire = null, $dependency = null, $enableCache = null)
    {
        if(is_null($enableCache))
        {
            $enableCache = craft()->config->get('enableCache', 'analytics');
        }

        if($enableCache)
        {
            $cacheKey = $this->getCacheKey($id);

            if(!$expire)
            {
                $expire = craft()->config->get('cacheDuration', 'analytics');
                $expire = AnalyticsHelper::formatDuration($expire);
            }

            return craft()->cache->set($cacheKey, $value, $expire, $dependency);
        }
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
