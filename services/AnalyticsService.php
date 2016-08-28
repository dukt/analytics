<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/base/AnalyticsTrait.php');

class AnalyticsService extends BaseApplicationComponent
{
	// Traits
	// =========================================================================

	use AnalyticsTrait;

	// Properties
	// =========================================================================

	private $tracking;

	// Public Methods
	// =========================================================================

	/**
	 * Get realtime refresh intervall
	 *
	 * @return int|null
	 */
	public function getRealtimeRefreshInterval()
	{
		$interval = craft()->config->get('realtimeRefreshInterval', 'analytics');

		if($interval)
		{
			return $interval;
		}
		else
		{
			$plugin = craft()->plugins->getPlugin('analytics');
			$settings = $plugin->getSettings();

			if(!empty($settings['realtimeRefreshInterval']))
			{
				return $settings['realtimeRefreshInterval'];
			}
		}
	}

	/**
	 * Get data source from its class name
	 *
	 * @param string $className
	 *
	 * @return mixed
	 */
	public function getDataSource($className = 'GoogleAnalytics')
	{
		$nsClassName = "\\Dukt\\Analytics\\DataSources\\$className";
		return new $nsClassName;
	}

	/**
	 * Returns the Google Analytics Profile ID
	 *
	 * @return string|null
	 */
	public function getProfileId()
	{
		$plugin = craft()->plugins->getPlugin('analytics');
		$settings = $plugin->getSettings();

		if(!empty($settings['profileId']))
		{
			return 'ga:'.$settings['profileId'];
		}
	}

	/**
	 * Send tracking data to Google Analytics.
	 *
	 * @param array $options
	 *
	 * @return AnalyticsTracking|null
	 */
	public function track($options)
	{
		if(!$this->tracking)
		{
			require_once(CRAFT_PLUGINS_PATH.'analytics/etc/craft/AnalyticsTracking.php');
			$this->tracking = new AnalyticsTracking($options);
		}

		return $this->tracking;
	}

	/**
	 * Sends a request based on Analytics_RequestCriteriaModel to Google Analytics' API.
	 *
	 * @param Analytics_RequestCriteriaModel $criteria
	 *
	 * @return string
	 */
	public function sendRequest(Analytics_RequestCriteriaModel $criteria)
	{
		$this->populateCriteria($criteria);

		return craft()->analytics_api->getReport(
			$criteria->ids,
			$criteria->startDate,
			$criteria->endDate,
			$criteria->metrics,
			$criteria->optParams,
			$criteria->enableCache
		);
	}

	/**
	 * Sends a request based on Analytics_RequestCriteriaModel to Google Analytics' Realtime API.
	 *
	 * @param Analytics_RequestCriteriaModel $criteria
	 *
	 * @return string
	 */
	public function sendRealtimeRequest(Analytics_RequestCriteriaModel $criteria)
	{
		$this->populateCriteria($criteria);

		return craft()->analytics_api->getRealtimeReport(
			$criteria->ids,
			$criteria->metrics,
			$criteria->optParams
		);
	}

	/**
	 * Get Element URL Path
	 *
	 * @param int           $elementId
	 * @param string|null   $localeId
	 */
	public function getElementUrlPath($elementId, $localeId)
	{
		$element = craft()->elements->getElementById($elementId, null, $localeId);

		$uri = $element->uri;
		$url = $element->url;

		$components = parse_url($url);

		if($components['path'])
		{
			$uri = $components['path'];
		}

		return $uri;
	}

	// Private Methods
	// =========================================================================

	/**
	 * Populate Criteria
	 *
	 * @param Analytics_RequestCriteriaModel $criteria
	 */
	private function populateCriteria($criteria)
	{
		// Profile ID

		$criteria->ids = craft()->analytics->getProfileId();


		// Filters

		$filters = [];

		if(isset($criteria->optParams['filters']))
		{
			$filters = $criteria->optParams['filters'];

			if(is_string($filters))
			{
				$filters = explode(";", $filters);
			}
		}

		$configFilters = craft()->config->get('filters', 'analytics');

		if($configFilters)
		{
			$filters = array_merge($filters, $configFilters);
		}

		if(count($filters) > 0)
		{
			$optParams = $criteria->optParams;

			$optParams['filters'] = implode(";", $filters);

			$criteria->optParams = $optParams;
		}
	}
}
