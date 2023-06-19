<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use Craft;
use craft\base\Element;
use craft\helpers\Json;
use craft\web\assets\d3\D3Asset;
use yii\base\Component;
use dukt\analytics\Plugin as AnalyticsPlugin;

class Analytics extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var bool|string|null Demo mode.
     */
    public $demoMode = false;

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
        }

        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
        $settings = $plugin->getSettings();

        if (!empty($settings['realtimeRefreshInterval'])) {
            return $settings['realtimeRefreshInterval'];
        }

        return null;
    }

    /**
     * Returns the element URL path.
     *
     *
     * @return string
     */
    public function getElementUrlPath(int $elementId, ?int $siteId): string
    {
        /** @var Element $element */
        $element = Craft::$app->elements->getElementById($elementId, null, $siteId);

        $url = $element->url;

        $components = parse_url($url);

        return substr($url, \strlen($components['scheme'].'://'.$components['host']));
    }

    /**
     * Returns the chart language.
     *
     * @return string
     */
    public function getChartLanguage()
    {
        $chartLanguage = Craft::t('analytics', 'analyticsChartLanguage');

        if ($chartLanguage == 'analyticsChartLanguage') {
            $chartLanguage = 'en';
        }

        return $chartLanguage;
    }

    /**
     * Returns the Analytics tracking object.
     *
     *
     * @throws \InvalidArgumentException
     *
     * @return \TheIconic\Tracking\GoogleAnalytics\Analytics
     */
    public function tracking(bool $isSsl = false, bool $isDisabled = false, array $options = [])
    {
        $userAgent = Craft::$app->getRequest()->getUserAgent();

        if (empty($userAgent)) {
            $userAgent = "User-Agent:Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13\r\n";
        }

        $referrer = Craft::$app->getRequest()->getReferrer();

        if (empty($referrer)) {
            $referrer = '';
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
     * @throws \yii\base\InvalidConfigException
     */
    public function checkPluginRequirements()
    {
        if ($this->isOauthProviderConfigured()) {
            return $this->isTokenSet();
        }

        return false;
    }

    /**
     * Get a currency definition, updated with the currency from the GA view.
     *
     * @param string|null $gaViewCurrency
     * @return array
     */
    public function getCurrencyDefinition(string $gaViewCurrency = null): array
    {
        // Get the currency definition from `d3-format`
        $d3Asset = new D3Asset();
        $localeDefinitionForCurrency = Json::decode($d3Asset->formatDef(Craft::getAlias('@analyticsLib/d3-format')));
        $currencyDefinition = $localeDefinitionForCurrency['currency'];

        // Define the currency symbol based on the GA view currency
        $currencySymbol = ($gaViewCurrency ? Craft::$app->locale->getCurrencySymbol($gaViewCurrency) : '$');

        // Update the currency definition with the new currency symbol
        foreach ($currencyDefinition as $key => $row) {
            if (!empty($row)) {
                // Todo: Check currency symbol replacement with arabic
                $pattern = '/[^\s]+/u';
                $replacement = $currencySymbol;
                $newRow = preg_replace($pattern, $replacement, $row);
                $currencyDefinition[$key] = $newRow;
            }
        }

        // Return the currency definition
        return $currencyDefinition;
    }

    // Private Methods
    // =========================================================================

    /**
     * Checks if the token is set.
     *
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    private function isTokenSet()
    {
        $token = AnalyticsPlugin::$plugin->getOauth()->getToken(false);
        return $token !== null;
    }


    /**
     * _getGclid get the `gclid` and sets the 'gclid' cookie
     */
    private function _getGclid()
    {
        $gclid = '';
        if (isset($_GET['gclid'])) {
            $gclid = $_GET['gclid'];
            if (!empty($gclid)) {
                setcookie('gclid', $gclid, time() + (10 * 365 * 24 * 60 * 60), '/');
            }
        }

        return $gclid;
    }

     /* -- _getGclid */

    /**
     * _gaParseCookie handles the parsing of the _ga cookie or setting it to a unique identifier
     *
     * @return string the cid
     */
    private function _gaParseCookie()
    {
        if (isset($_COOKIE['_ga'])) {
            [$version, $domainDepth, $cid1, $cid2] = preg_split('[\.]', $_COOKIE['_ga'], 4);
            $contents = ['version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1.'.'.$cid2];
            $cid = $contents['cid'];
        } elseif (isset($_COOKIE['_ia']) && $_COOKIE['_ia'] != '') {
            $cid = $_COOKIE['_ia'];
        } else {
            $cid = $this->_gaGenUUID();
        }

        setcookie('_ia', $cid, time() + 60 * 60 * 24 * 730, '/'); // Two years

        return $cid;
    }

     /* -- _gaParseCookie */

    /**
     * _gaGenUUID Generate UUID v4 function - needed to generate a CID when one isn't available
     *
     * @return string The generated UUID
     */
    private function _gaGenUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            random_int(0, 0xffff), random_int(0, 0xffff),
            // 16 bits for "time_mid"
            random_int(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            random_int(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            random_int(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

     /* -- _gaGenUUID */
}
