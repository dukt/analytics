<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use dukt\Analytics\Plugin as AnalyticsPlugin;

class Analytics extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the real time refresh interval.
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
     * Returns the element URL path.
     *
     * @param int      $elementId
     * @param int|null $siteId
     *
     * @return string
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
     * Returns D3 format locale definition.
     *
     * @param array $options
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

        $formatLocalePath = Craft::getAlias('@lib')."/d3-format/{$d3Language}.json";

        $localeDefinition = json_decode(file_get_contents($formatLocalePath), true);

        if(isset($options['currency'])) {
            $localeDefinition['currency'] = $this->getD3LocaleDefinitionCurrency($options['currency']);
        }

        return $localeDefinition;
    }

    /**
     * Returns the chart language.
     *
     * @return string
     */
    public function getChartLanguage()
    {
        $chartLanguage = Craft::t('analytics', 'analyticsChartLanguage');

        if($chartLanguage == 'analyticsChartLanguage') {
            $chartLanguage = 'en';
        }

        return $chartLanguage;
    }

    /**
     * Returns the Analytics tracking object.
     *
     * @param bool $isSsl
     * @param bool $isDisabled
     * @param array $options
     * @throws \InvalidArgumentException
     *
     * @return \TheIconic\Tracking\GoogleAnalytics\Analytics
     */
    public function tracking($isSsl = false, $isDisabled = false, array $options = [])
    {
        $userAgent = Craft::$app->getRequest()->getUserAgent();

        if (empty($userAgent)) {
            $userAgent = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13\r\n";
        }

        $referrer = Craft::$app->getRequest()->getReferrer();

        if (empty($referrer)) {
            $referrer = "";
        }

        $analyticsTracking = new \TheIconic\Tracking\GoogleAnalytics\Analytics($isSsl, $isDisabled, $options);
        $analyticsTracking
            ->setProtocolVersion('1')
            ->setUserAgentOverride($userAgent)
            ->setDocumentHostName(Craft::$app->getRequest()->getServerName())
            ->setDocumentReferrer($referrer)
            ->setAsyncRequest(false)
            ->setClientId($this->_gaParseCookie());

        return $analyticsTracking;
    }

    /**
     * Checks if the OAuth provider is configured.
     *
     * @return bool
     */
    public function isOauthProviderConfigured()
    {
        $oauthClientId = AnalyticsPlugin::$plugin->getSettings()->oauthClientId;
        $oauthClientSecret = AnalyticsPlugin::$plugin->getSettings()->oauthClientSecret;

        return !empty($oauthClientId) && !empty($oauthClientSecret);
    }

    /**
     * Checks plugin requirements (dependencies, configured OAuth provider, and token).
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
     * Returns D3 currency format locale definition.
     *
     * @param string $currency
     *
     * @return array
     */
    private function getD3LocaleDefinitionCurrency(string $currency)
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
     * Checks if the token is set.
     *
     * @return bool
     */
    private function isTokenSet()
    {
        $token = AnalyticsPlugin::$plugin->getOauth()->getToken(false);

        if ($token) {
            return true;
        }

        return false;
    }


    /**
     * _getGclid get the `gclid` and sets the 'gclid' cookie
     */
    private function _getGclid()
    {
        $gclid = "";
        if (isset($_GET['gclid']))
        {
            $gclid = $_GET['gclid'];
            if (!empty($gclid))
            {
                setcookie("gclid", $gclid, time() + (10 * 365 * 24 * 60 * 60),  "/");
            }
        }
        return $gclid;
    } /* -- _getGclid */

    /**
     * _gaParseCookie handles the parsing of the _ga cookie or setting it to a unique identifier
     * @return string the cid
     */
    private function _gaParseCookie()
    {
        if (isset($_COOKIE['_ga']))
        {
            list($version, $domainDepth, $cid1, $cid2) = preg_split('[\.]', $_COOKIE["_ga"], 4);
            $contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
            $cid = $contents['cid'];
        }
        else
        {
            if (isset($_COOKIE['_ia']) && $_COOKIE['_ia'] !='' )
                $cid = $_COOKIE['_ia'];
            else
                $cid = $this->_gaGenUUID();
        }
        setcookie('_ia', $cid, time()+60*60*24*730, "/"); // Two years
        return $cid;
    } /* -- _gaParseCookie */

    /**
     * _gaGenUUID Generate UUID v4 function - needed to generate a CID when one isn't available
     * @return string The generated UUID
     */
    private function _gaGenUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    } /* -- _gaGenUUID */
}
