<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use dukt\analytics\etc\craft\AnalyticsTracking;
use dukt\Analytics\Plugin as AnalyticsPlugin;

class Analytics extends Component
{
    // Properties
    // =========================================================================

    private $tracking;

    // Public Methods
    // =========================================================================

    /**
     * Get real time refresh interval
     *
     * @return int|null
     */
    public function getRealtimeRefreshInterval()
    {
        $interval = AnalyticsPlugin::$plugin->getSettings()->realtimeRefreshInterval;

        if ($interval) {
            return $interval;
        } else {
            $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
            $settings = $plugin->getSettings();

            if (!empty($settings['realtimeRefreshInterval'])) {
                return $settings['realtimeRefreshInterval'];
            }
        }

        return null;
    }

    /**
     * Get Element URL Path
     *
     * @param int           $elementId
     * @param int|null      $siteId
     */
    public function getElementUrlPath($elementId, $siteId)
    {
        $element = Craft::$app->elements->getElementById($elementId, null, $siteId);

        $uri = $element->uri;
        $url = $element->url;

        $components = parse_url($url);

        if ($components['path']) {
            $uri = $components['path'];
        }

        if (Craft::$app->getConfig()->getGeneral()->addTrailingSlashesToUrls) {
            $uri .= '/';
        }

        return $uri;
    }

    /**
     * Returns a currency
     *
     * @param string|null $currency
     */
    public function getCurrency()
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');

        $settings = $plugin->getSettings();

        if (!empty($settings['profileCurrency'])) {
            return $settings['profileCurrency'];
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

        if (strpos($currencyFormat, ";") > 0) {
            $currencyFormatArray = explode(";", $currencyFormat);
            $currencyFormat = $currencyFormatArray[0];
        }

        $pattern = '/[#0,.]/';
        $replacement = '';
        $currencyFormat = preg_replace($pattern, $replacement, $currencyFormat);

        if (strpos($currencyFormat, "¤") === 0) {
            // symbol at beginning
            $currencyD3Format = [str_replace('¤', $currencySymbol, $currencyFormat), ''];
        } else {
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
        if (!$this->tracking) {
            $this->tracking = new AnalyticsTracking($options);
        }

        return $this->tracking;
    }

    /**
     * Checks if the OAuth provider is configured
     *
     * @return bool
     */
    public function isOauthProviderConfigured()
    {
        $oauthClientId = AnalyticsPlugin::$plugin->getSettings()->oauthClientId;
        $oauthClientSecret = AnalyticsPlugin::$plugin->getSettings()->oauthClientSecret;

        if (!empty($oauthClientId) && !empty($oauthClientSecret)) {
            return true;
        }

        return false;
    }

    /**
     * Checks plugin requirements (dependencies, configured OAuth provider, and token)
     *
     * @return bool
     */
    public function checkPluginRequirements()
    {
        if ($this->isOauthProviderConfigured()) {
            if ($this->isTokenSet()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function isTokenSet()
    {
        $token = AnalyticsPlugin::$plugin->getOauth()->getToken();

        if ($token) {
            return true;
        } else {
            return false;
        }
    }
}
