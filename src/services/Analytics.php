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
     * Returns D3 currency format locale definition.
     *
     * @return string
     */
    public function getD3LocaleDefinitionCurrency(string $currency)
    {
        $currencySymbol = ($currency ? Craft::$app->locale->getCurrencySymbol($currency) : '$');

        $localeDefinition = $this->getD3LocaleDefinition();

        $currencyDefinition = $localeDefinition['currency'];

        foreach($currencyDefinition as $key => $row) {
            if(!empty($row)) {
                // Todo: Check currency symbol replacement with arabic

                $pattern = '/[^\s]+/u';
                $replacement = $currencySymbol;
                $newRow = preg_replace($pattern, $replacement, $row);

                $currencyDefinition[$key] = $newRow;
            }
        }

        return $currencyDefinition;
    }

    /**
     * Returns D3 format locale definition.
     *
     * @return array
     */
    public function getD3LocaleDefinition(array $options = [])
    {
        // Figure out which D3 i18n script to load

        $language = Craft::$app->language;

        if (in_array($language, ['ca-ES', 'de-CH', 'de-DE', 'en-CA', 'en-GB', 'en-US', 'es-ES', 'fi-FI', 'fr-CA', 'fr-FR', 'he-IL', 'hu-HU', 'it-IT', 'ja-JP', 'ko-KR', 'nl-NL', 'pl-PL', 'pt-BR', 'ru-RU', 'sv-SE', 'zh-CN'], true)) {
            $d3Language = $language;
        } else {
            $languageId = Craft::$app->getLocale()->getLanguageID();

            $d3LanguageIds = [
                'ca' => 'ca-ES',
                'de' => 'de-DE',
                'en' => 'en-US',
                'es' => 'es-ES',
                'fi' => 'fi-FI',
                'fr' => 'fr-FR',
                'he' => 'he-IL',
                'hu' => 'hu-HU',
                'it' => 'it-IT',
                'ja' => 'ja-JP',
                'ko' => 'ko-KR',
                'nl' => 'nl-NL',
                'pl' => 'pl-PL',
                'pt' => 'pt-BR',
                'ru' => 'ru-RU',
                'sv' => 'sv-SE',
                'zh' => 'zh-CN',
            ];

            if (array_key_exists($language, $d3LanguageIds)) {
                $d3Language = $d3LanguageIds[$language];
            } else {
                if (array_key_exists($languageId, $d3LanguageIds)) {
                    $d3Language = $d3LanguageIds[$languageId];
                } else {
                    $d3Language = 'en-US';
                }
            }
        }

        $formatLocalePath = Craft::getAlias('@bower')."/d3-format/locale/{$d3Language}.json";

        $localeDefinition = json_decode(file_get_contents($formatLocalePath), true);

        if(isset($options['currency'])) {
            $localeDefinition['currency'] = $this->getD3LocaleDefinitionCurrency($options['currency']);
        }

        return $localeDefinition;
    }

    public function getChartLanguage()
    {
        $chartLanguage = Craft::t('analytics', 'analyticsChartLanguage');

        if($chartLanguage == 'analyticsChartLanguage') {
            $chartLanguage = 'en';
        }

        return $chartLanguage;
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

    // Private Methods
    // =========================================================================

    /**
     * Checks if token is set.
     * 
     * @return bool
     */
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
