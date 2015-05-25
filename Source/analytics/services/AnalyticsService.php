<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use \Google_Client;
use \Google_Service_Analytics;

class AnalyticsService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $oauthHandle = 'google';
    private $token;

    // Public Methods
    // =========================================================================

    /**
     * Request
     */
    public function request($attributes)
    {
        return new Analytics_RequestCriteriaModel($attributes);
    }

    /**
     * Send Request
     */
    public function sendRequest(Analytics_RequestCriteriaModel $criteria)
    {
        $profile = craft()->analytics->getProfile();

        $criteria->ids = 'ga:'.$profile['id'];

        if($criteria->realtime)
        {
            $response = $this->apiGetGADataRealtime(
                $criteria->ids,
                $criteria->metrics,
                $criteria->options
            );
        }
        else
        {
            $response = $this->apiGetGAData(
                $criteria->ids,
                $criteria->startDate,
                $criteria->endDate,
                $criteria->metrics,
                $criteria->options
            );
        }

        if($criteria->format == 'gaData')
        {
            return $response;
        }
        else
        {
            return AnalyticsHelper::gaDataToArray($response);
        }
    }

    /**
     * Save Token
     *
     * @param Oauth_TokenModel $token
     */
    public function saveToken(Oauth_TokenModel $token)
    {
        // get plugin
        $plugin = craft()->plugins->getPlugin('analytics');

        // get settings
        $settings = $plugin->getSettings();


        // do we have an existing token ?

        $existingToken = craft()->oauth->getTokenById($settings->tokenId);

        if($existingToken)
        {
            $token->id = $existingToken->id;
        }

        // save token
        craft()->oauth->saveToken($token);

        // set token ID
        $settings->tokenId = $token->id;

        // save plugin settings
        craft()->plugins->savePluginSettings($plugin, $settings);
    }

    /**
     * Get OAuth Token
     */
    public function getToken()
    {
        if($this->token)
        {
            return $this->token;
        }
        else
        {
            // get plugin
            $plugin = craft()->plugins->getPlugin('analytics');

            // get settings
            $settings = $plugin->getSettings();

            // get tokenId
            $tokenId = $settings->tokenId;

            // get token
            $token = craft()->oauth->getTokenById($tokenId);

            return $token;
        }
    }

    /**
     * Delete Token
     */
    public function deleteToken()
    {
        // get plugin
        $plugin = craft()->plugins->getPlugin('analytics');

        // get settings
        $settings = $plugin->getSettings();

        if($settings->tokenId)
        {
            $token = craft()->oauth->getTokenById($settings->tokenId);

            if($token)
            {
                if(craft()->oauth->deleteToken($token))
                {
                    $settings->tokenId = null;

                    craft()->plugins->savePluginSettings($plugin, $settings);

                    return true;
                }
            }
        }

        return false;
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
     * Get Profile
     */
    public function getProfile()
    {
        $r = array();

        $webProperty = $this->getWebProperty();

        $profile = craft()->fileCache->get('analytics.profile');

        if(!$profile && !empty($webProperty['accountId']))
        {
            $profiles = $this->getApiObject()->management_profiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

            $profile = $profiles['items'][0];

            craft()->fileCache->set('analytics.profile', $profile);
        }

        if($profile)
        {
            return $profile;
        }
        else
        {
            throw new Exception("Couldn't get profile");
        }

        return $r;
    }

    /**
     * Get Web Property
     */
    public function getWebProperty()
    {
        $r = array();

        try {

            $webProperty = craft()->fileCache->get('analytics.webProperty');

            if(!$webProperty) {

                $api = $this->getApiObject();

                if($api)
                {
                    $webProperties = $this->getApiObject()->management_webproperties->listManagementWebproperties("~all");

                    foreach($webProperties['items'] as $webPropertyItem) {

                        if($webPropertyItem['id'] == $this->getSetting('profileId')) {
                            $webProperty = $webPropertyItem;
                        }
                    }

                    if($webProperty)
                    {
                        craft()->fileCache->set('analytics.webProperty', $webProperty);
                    }
                }
            }

            $r = $webProperty;

        } catch(\Exception $e) {
            $r['error'] = $e->getMessage();
        }

        return $r;
    }

    /**
     * Get Properties Opts
     */
    public function getPropertiesOpts()
    {
        $properties = array("" => Craft::t("Select"));

        $api = craft()->analytics->getApiObject();

        if(!$api)
        {
            Craft::log(__METHOD__.' : Could not get API', LogLevel::Info, true);
            return false;
        }

        $response = $api->management_webproperties->listManagementWebproperties("~all");

        if(!$response)
        {
            Craft::log(__METHOD__.' : Could not list management web properties', LogLevel::Info, true);
            return false;
        }

        $items = $response['items'];

        foreach($items as $item)
        {
            $name = $item['id'];

            if(!empty($item['websiteUrl']))
            {
                $name .= ' - '.$item['websiteUrl'];
            }
            elseif(!empty($item['name']))
            {
                $name .= ' - '.$item['name'];
            }

            $properties[$item['id']] = $name;
        }

        return $properties;
    }

    /**
     * Get Setting
     *
     * @param string $key
     */
    public function getSetting($key)
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        return $settings[$key];
    }

    /**
     * Get a dimension or a metric from its key
     *
     * @param string $key
     */
    public function getDimMet($key)
    {
        $dimsmets = $this->getData('dimsmets');

        if(!empty($dimsmets[$key]))
        {
            return $dimsmets[$key];
        }
    }

    /**
     * Get Browser Sections
     *
     * @param bool $json
     */
    public function getBrowserSections($json = false)
    {
        $browserSections = $this->getData('browserSections');

        foreach($browserSections as $k => $browserSection)
        {
            $browserSections[$k]['title'] = Craft::t($browserSections[$k]['title']);
        }

        if($json)
        {
            return json_encode($browserSections);
        }
        else
        {
            return $browserSections;
        }
    }

    /**
     * Get Browser Data
     *
     * @param bool $json
     */
    public function getBrowserData($json = false)
    {
        $browserData = $this->getData('browserData');

        foreach($browserData as $k => $row)
        {
            $browserData[$k]['title'] = Craft::t($browserData[$k]['title']);

            if(!empty($browserData[$k]['metrics']))
            {
                foreach($browserData[$k]['metrics'] as $k2 => $metric)
                {
                    $label = $this->getDimMet($metric);
                    $label = Craft::t($label);

                    $browserData[$k]['metrics'][$k2] = array(
                        'label' => $label,
                        'value' => $metric
                    );
                }
            }

            if(!empty($browserData[$k]['dimensions']))
            {
                foreach($browserData[$k]['dimensions'] as $k2 => $dimension)
                {
                    $label = $this->getDimMet($dimension);
                    $label = Craft::t($label);

                    $browserData[$k]['dimensions'][$k2] = array(
                        'label' => $label,
                        'value' => $dimension
                    );
                }
            }
        }

        if($json)
        {
            return json_encode($browserData);
        }
        else
        {
            return $browserData;
        }
    }

    /**
     * Get Browser Select
     */
    public function getBrowserSelect()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginSettings = $plugin->getSettings();

        $browserSelect = array();

        if($pluginSettings->enableRealtime)
        {
            $browserSelect = array_merge($browserSelect, $this->getData('browserSelectRealtime'));
        }

        $browserSelect = array_merge($browserSelect, $this->getData('browserSelect'));

        foreach($browserSelect as $k => $row)
        {
            if(!empty($browserSelect[$k]['optgroup']))
            {
                $browserSelect[$k]['optgroup'] = Craft::t($browserSelect[$k]['optgroup']);
            }

            if(!empty($browserSelect[$k]['label']))
            {
                $browserSelect[$k]['label'] = Craft::t($browserSelect[$k]['label']);
            }
        }

        return $browserSelect;
    }

    /**
     * Get Language
     */
    public function getLanguage()
    {
        return craft()->language;
    }

    /**
     * Get Continent Code
     *
     * @param string $label
     */
    public function getContinentCode($label)
    {
        $continents = $this->getData('continents');

        foreach($continents as $continent)
        {
            if($continent['label'] == $label)
            {
                return $continent['code'];
            }
        }
    }

    /**
     * Get Sub-Continent Code
     *
     * @param string $label
     */
    public function getSubContinentCode($label)
    {
        $subContinents = $this->getData('subContinents');

        foreach($subContinents as $subContinent)
        {
            if($subContinent['label'] == $label)
            {
                return $subContinent['code'];
            }
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Get API Object
     */
    private function getApiObject()
    {
        $handle = $this->oauthHandle;

        // provider

        $provider = craft()->oauth->getProvider($handle);

        if($provider)
        {

            // token
            $token = craft()->analytics->getToken();

            if ($token)
            {
                // make token compatible with Google library
                $arrayToken = array(
                    'created' => 0,
                    'access_token' => $token->accessToken,
                    'expires_in' => $token->endOfLife,
                );

                $arrayToken = json_encode($arrayToken);

                // client
                $client = new Google_Client();
                $client->setApplicationName('Google+ PHP Starter Application');
                $client->setClientId('clientId');
                $client->setClientSecret('clientSecret');
                $client->setRedirectUri('redirectUri');
                $client->setAccessToken($arrayToken);

                $api = new Google_Service_Analytics($client);

                return $api;
            }
            else
            {
                Craft::log(__METHOD__.' : No token defined', LogLevel::Info, true);
                return false;
            }
        }
        else
        {
            Craft::log(__METHOD__.' : Could not get provider connected', LogLevel::Info, true);
            return false;
        }
    }

    /**
     * API Get GA Data
     *
     * @param string|null   $p1
     * @param string|null   $p2
     * @param string|null   $p3
     * @param string|null   $p4
     * @param array         $p5
     */
    private function apiGetGAData($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = array(), $realtime = false)
    {
        $enableCache = true;
        $cacheDuration = $this->cacheDuration();
        $api = $this->getApiObject()->data_ga;

        if($realtime)
        {
            $cacheDuration = $this->getSetting('realtimeRefreshInterval');
            $api = $this->getApiObject()->data_realtime;
        }

        if(craft()->config->get('disableAnalyticsCache') === null)
        {
            if(craft()->config->get('disableAnalyticsCache', 'analytics') === true)
            {
                $enableCache = false;
            }
        }
        else
        {
            if(craft()->config->get('disableAnalyticsCache') === true)
            {
                $enableCache = false;
            }
        }

        if($enableCache)
        {
            $cacheKey = 'analytics/explorer/'.md5(serialize(array($p1, $p2, $p3, $p4, $p5)));
            $response = craft()->fileCache->get($cacheKey);

            if(!$response)
            {
                if($realtime)
                {
                    $response = $api->get($p1, $p2, $p3, $p4, $p5);
                }
                else
                {
                    $response = $api->get($p1, $p2, $p3, $p4, $p5);
                }


                craft()->fileCache->set($cacheKey, $response, $cacheDuration);
            }
        }
        else
        {
            $response = $api->get($p1, $p2, $p3, $p4, $p5);
        }

        return $response;
    }

    /**
     * API Get GA Data Realtime
     *
     * @param string|null   $p1 ids
     * @param string|null   $p2 metrics
     * @param string|null   $p3 optParams
     */
    private function apiGetGADataRealtime($p1 = null, $p2 = null, $p3 = array())
    {
        $enableCache = true;
        $cacheDuration = $this->getSetting('realtimeRefreshInterval');
        $api = $this->getApiObject()->data_realtime;

        if(craft()->config->get('disableAnalyticsCache') === null)
        {
            if(craft()->config->get('disableAnalyticsCache', 'analytics') === true)
            {
                $enableCache = false;
            }
        }
        else
        {
            if(craft()->config->get('disableAnalyticsCache') === true)
            {
                $enableCache = false;
            }
        }

        if($enableCache)
        {
            $cacheKey = 'analytics/explorer/'.md5(serialize(array($p1, $p2, $p3, $p4, $p5)));
            $response = craft()->fileCache->get($cacheKey);

            if(!$response)
            {
                $response = $api->get($p1, $p2, $p3);
                craft()->fileCache->set($cacheKey, $response, $cacheDuration);
            }
        }
        else
        {
            $response = $api->get($p1, $p2, $p3);
        }

        return $response;
    }

    /**
     * Cache Duration
     */
    private function cacheDuration()
    {
        $cacheDuration = craft()->config->get('analyticsCacheDuration');

        if(!$cacheDuration)
        {
            // default value
            $cacheDuration = craft()->config->get('analyticsCacheDuration', 'analytics');
        }

        $cacheDuration = new DateInterval($cacheDuration);
        $cacheDurationSeconds = $cacheDuration->format('%s');

        return $cacheDurationSeconds;
    }

    /**
     * Get Data
     *
     * @param string $label
     */
    private function getData($name)
    {
        $jsonData = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/'.$name.'.json');
        $data = json_decode($jsonData, true);

        return $data;
    }
}
