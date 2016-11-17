<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_CacheService extends CacheService
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
		if(craft()->config->get('enableCache', 'analytics') == true)
		{
			$cacheKey = $this->getCacheKey($id);

			return parent::get($cacheKey);
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
			$enableCache = craft()->config->get('enableCache', 'analytics');
		}

		if($enableCache)
		{
			$cacheKey = $this->getCacheKey($id);

			if(!$expire)
			{
				$expire = $this->getCacheDuration();
			}

			return parent::set($cacheKey, $value, $expire, $dependency);
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

        return parent::delete($cacheKey);
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
		$duration = craft()->config->get('cacheDuration', 'analytics');
		return DateTimeHelper::timeFormatToSeconds($duration);
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
