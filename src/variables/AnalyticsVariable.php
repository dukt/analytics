<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use dukt\social\Plugin as Social;

class AnalyticsVariable
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns a Analytics_RequestCriteriaModel model that can be sent to request Google Analytics' API.
	 *
	 * @param array $attributes
	 *
	 * @return Analytics_RequestCriteriaModel
	 */
	public function api($attributes = null)
	{
		return new Analytics_RequestCriteriaModel($attributes);
	}

	/**
	 * Sends tracking data to Google Analytics.
	 *
	 * @param array $options
	 *
	 * @return AnalyticsTracking|null
	 */
	public function track($options = null)
	{
		return Social::$plugin->analytics->track($options);
	}

	public function getProfileId()
	{
		return Social::$plugin->analytics->getProfileId();
	}
}
