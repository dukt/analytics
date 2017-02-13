<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use dukt\analytics\base\RequirementsTrait;
use dukt\analytics\etc\craft\AnalyticsTracking;

class Analytics extends Component
{
	// Traits
	// =========================================================================

	use RequirementsTrait;

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
		$interval = Craft::$app->config->get('realtimeRefreshInterval', 'analytics');

		if($interval)
		{
			return $interval;
		}
		else
		{
			$plugin = Craft::$app->plugins->getPlugin('analytics');
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
		$plugin = Craft::$app->plugins->getPlugin('analytics');
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
		// $element = Craft::$app->elements->getElementById($elementId, null, $localeId);
        $element = Craft::$app->elements->getElementById($elementId);

		$uri = $element->uri;
		$url = $element->url;

		$components = parse_url($url);

		if($components['path'])
		{
			$uri = $components['path'];
		}

        if(Craft::$app->config->get('addTrailingSlashesToUrls'))
        {
            $uri .= '/';
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
		$plugin = Craft::$app->plugins->getPlugin('analytics');

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
		$currency = $this->getCurrency();

		$currencySymbol = ($currency ? Craft::$app->locale->getCurrencySymbol($currency) : '$');
		// $currencyFormat = Craft::$app->locale->getCurrencyFormat();
		$currencyFormat = '$,.2f';

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
            $this->tracking = new AnalyticsTracking($options);
        }

        return $this->tracking;
    }
}
