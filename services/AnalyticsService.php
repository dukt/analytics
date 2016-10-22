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

    /**
     * Returns a currency
     *
     * @param string|null   $currency
     */
	public function getCurrency()
	{
		$plugin = craft()->plugins->getPlugin('analytics');
		$settings = $plugin->getSettings();

		if(!empty($settings['currency']))
		{
			return $settings['currency'];
		}
	}

	/**
	 * Returns D3 currency format locale definition.
	 *
	 * @return string
	 */
	public function getD3LocaleDefinitionCurrency()
	{
		$currency = craft()->analytics->getCurrency();

		$currencySymbol = craft()->locale->getCurrencySymbol($currency);
		$currencyFormat = craft()->locale->getCurrencyFormat();

		if(strpos($currencyFormat, ";") > 0)
		{
			$currencyFormatArray = explode(";", $currencyFormat);
			$currencyFormat = $currencyFormatArray[0];
		}

		$pattern = '/[#0,.]/';
		$replacement = '';
		$currencyFormat = preg_replace($pattern, $replacement, $currencyFormat);

		if(strpos($currencyFormat, "¤") === 0)
		{
			// symbol at beginning
			$currencyD3Format = [str_replace('¤', $currencySymbol, $currencyFormat), ''];
		}
		else
		{
			// symbol at the end
			$currencyD3Format = ['', str_replace('¤', $currencySymbol, $currencyFormat)];
		}

		return $currencyD3Format;
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
}
