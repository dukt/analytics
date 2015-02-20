<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

use \Google_Client;
use \Google_Service_Analytics;

class AnalyticsService extends BaseApplicationComponent
{
    private $oauthHandle = 'google';
    private $token;

    /**
     * Get a dimension or a metric from its key
     */
    public function getDimMet($key)
    {
        $dimsmetsJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/dimsmets.json');
        $dimsmets = json_decode($dimsmetsJson, true);

        if(!empty($dimsmets[$key]))
        {
            return $dimsmets[$key];
        }
    }

    public function getBrowserSections($json = false)
    {
        $browserSectionsJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/browserSections.json');
        $browserSections = json_decode($browserSectionsJson, true);

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

    public function getBrowserData($json = false)
    {
        $browserDataJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/browserData.json');
        $browserData = json_decode($browserDataJson, true);

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

    public function getBrowserSelect()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginSettings = $plugin->getSettings();

        $browserSelect = array();

        if($pluginSettings->enableRealtime)
        {
            $browserSelectRealtimeJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/browserSelectRealtime.json');
            $browserSelect = array_merge($browserSelect, json_decode($browserSelectRealtimeJson, true));
        }

        $browserSelectJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/browserSelect.json');
        $browserSelect = array_merge($browserSelect, json_decode($browserSelectJson, true));

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

    public function getLanguage()
    {
        return craft()->language;
    }

    public function getContinentCode($label)
    {
        $continentsJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/continents.json');
        $continents = json_decode($continentsJson, true);

        foreach($continents as $continent)
        {
            if($continent['label'] == $label)
            {
                return $continent['code'];
            }
        }
    }

    public function getSubContinentCode($label)
    {
        $subContinentsJson = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/subContinents.json');
        $subContinents = json_decode($subContinentsJson, true);

        foreach($subContinents as $subContinent)
        {
            if($subContinent['label'] == $label)
            {
                return $subContinent['code'];
            }
        }
    }

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

    public function getElementUrlPath($elementId, $locale)
    {
        $element = craft()->elements->getElementById($elementId, null, $locale);

        $uri = $element->uri;
        $url = $element->url;

        $components = parse_url($url);

        if($components['path'])
        {
            $uri = $components['path'];
        }

        return $uri;
    }

    public function api($options)
    {
        try
        {
            $profile = craft()->analytics->getProfile();

            $response = array(
                'cols' => array(),
                'rows' => array(),
                'success' => false,
                'error' => false
            );

            $ids = 'ga:'.$profile['id'];
            $start = null;
            $end = null;
            $metrics = null;

            if(isset($options['start-date']))
            {
                $start = $options['start-date'];
                unset($options['start-date']);
            }

            if(isset($options['start-date']))
            {
                $end = $options['end-date'];
                unset($options['end-date']);
            }

            if(isset($options['start-date']))
            {
                $metrics = $options['metrics'];
                unset($options['metrics']);
            }

            // request

            $apiResponse = null;
            $enableCache = true;

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
                $cacheKey = 'analytics/template/'.md5(serialize(array(
                    $ids,
                    $start,
                    $end,
                    $metrics,
                    $options
                )));

                $apiResponse = craft()->fileCache->get($cacheKey);
            }

            if(!$apiResponse)
            {
                $apiResponse = $this->getApiObject()->data_ga->get($ids, $start, $end, $metrics, $options);

                if($enableCache)
                {
                    craft()->fileCache->set($cacheKey, $apiResponse, $this->cacheDuration());
                }
            }

            if($apiResponse)
            {
                $response['cols'] = $apiResponse['columnHeaders'];
                $response['rows'] = $apiResponse['rows'];
                $response['success'] = true;
            }
            else
            {
                throw new Exception("Couldn't get API response");
            }
        }
        catch(\Exception $e)
        {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();
        }

        return $response;
    }

    public function formatTime($seconds)
    {
        return gmdate("H:i:s", $seconds);
    }

    private function formatCell($value, $column)
    {
        switch($column['name'])
        {
            case "ga:avgTimeOnPage":
                $value = $this->formatTime($value);
                return $value;
                break;

            case 'ga:pageviewsPerSession':
                $value = round($value, 2);
                return $value;
                break;

            case 'ga:entranceRate':
            case 'ga:visitBounceRate':
            case 'ga:exitRate':
                $value = round($value, 2)."%";
                return $value;
                break;

            default:
                return $value;
        }
    }

    public function apiGet($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = array())
    {
        $enableCache = true;

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

            $return = craft()->fileCache->get($cacheKey);

            if(!$return)
            {
                $response = $this->getApiObject()->data_ga->get($p1, $p2, $p3, $p4, $p5);

                $return = $this->parseApiResponse($response);

                $cacheDuration = $this->cacheDuration();

                craft()->fileCache->set($cacheKey, $return, $cacheDuration);
            }
            return $return;
        }
        else
        {
            $response = $this->getApiObject()->data_ga->get($p1, $p2, $p3, $p4, $p5);
            return $this->parseApiResponse($response);
        }
    }

    public function cacheDuration()
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

    public function parseApiResponse($apiResponse)
    {
        $response = array();

        $cols = $apiResponse->columnHeaders;
        $rows = $apiResponse->rows;

        $cols = $this->localizeColumns($cols);
        $rows = $this->parseRows($cols, $rows);

        return array(
            'columns' => $cols,
            'rows' => $rows
        );
    }

    public function apiRealtimeGet($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = array())
    {
        $response = craft()->analytics->getApiObject()->data_realtime->get($p1, $p2, $p3, $p4, $p5);


        $enableCache = true;

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
            $cacheKey = 'analytics/realtime/'.md5(serialize(array($p1, $p2, $p3, $p4, $p5)));

            $return = craft()->fileCache->get($cacheKey);

            if(!$return)
            {
                $return = $this->parseRealTimeApiResponse($response);

                craft()->fileCache->set($cacheKey, $return, $this->getSetting('realtimeRefreshInterval'));
            }

            return $return;
        }
        else
        {
            return $this->parseRealTimeApiResponse($response);
        }
    }

    public function parseRealTimeApiResponse($response)
    {
        $cols = $response['columnHeaders'];
        $rows = $response->rows;

        $cols = $this->localizeColumns($cols);
        $rows = $this->parseRows($cols, $rows);

        return array(
            'columns' => $cols,
            'rows' => $rows
        );
    }

    private function localizeColumns($cols)
    {
        foreach($cols as $key => $col)
        {
            $cols[$key]->label = Craft::t($col->name);

            switch($col->name)
            {
                case 'ga:latitude':
                $cols[$key]['columnType'] = 'LATITUDE';
                $cols[$key]['dataType'] = 'FLOAT';
                $cols[$key]['name'] = 'Latitude';
                $cols[$key]['label'] = 'Latitude';
                break;
                case 'ga:longitude':
                $cols[$key]['columnType'] = 'LONGITUDE';
                $cols[$key]['dataType'] = 'FLOAT';
                $cols[$key]['name'] = 'Longitude';
                $cols[$key]['label'] = 'Longitude';
                break;
            }
        }

        return $cols;
    }

    private function parseRows($cols, $apiRows = null)
    {
        $rows = array();

        if($apiRows)
        {
            foreach($apiRows as $apiRow)
            {
                $row = array();

                $colNumber = 0;

                foreach($apiRow as $key => $value)
                {
                    $col = $cols[$colNumber];
                    $value = $this->formatRawValue($col->dataType, $value);

                    $cell = array(
                        'v' => $value,
                        'f' => (string) $this->formatValue($col->dataType, $value)
                    );

                    switch($col->name)
                    {
                        case 'ga:date':
                        $cell = strftime("%Y.%m.%d", strtotime($value));
                        break;

                        case 'ga:latitude':
                        case 'ga:longitude':
                        $cell = (float) $value;
                        break;

                        case 'ga:yearMonth':
                        $cell = strftime("%Y.%m.%d", strtotime($value.'01'));
                        break;
                    }

                    array_push($row, $cell);

                    $colNumber++;
                }

                array_push($rows, $row);
            }
        }
        return $rows;
    }


    private function formatRawValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'CURRENCY':
            case 'FLOAT':
            case 'TIME':
            case 'PERCENT':
            $value = (float) $value;
            break;

            default:
            $value = (string) $value;
        }

        return $value;
    }

    private function formatValue($type, $value)
    {
        switch($type)
        {
            case 'INTEGER':
            case 'CURRENCY':
            case 'FLOAT':
            $value = (float) $value;
            $value = round($value, 2);
            break;

            case 'TIME':
            $value = (float) $value;
            $value = $this->formatTime($value);
            break;

            case 'PERCENT':
            $value = (float) $value;
            $value = round($value, 2);
            $value = $value.'%';

            break;

            default:
            $value = (string) $value;
        }

        return $value;
    }

    public function getApiObject()
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

    public function getPropertiesOpts()
    {

        $properties = array("" => Craft::t("Select"));


        $api = craft()->analytics->getApiObject();

        if(!$api) {

            Craft::log(__METHOD__.' : Could not get API', LogLevel::Info, true);

            return false;
        }

        $response = $api->management_webproperties->listManagementWebproperties("~all");

        if(!$response) {
            Craft::log(__METHOD__.' : Could not list management web properties', LogLevel::Info, true);
            return false;
        }
        $items = $response['items'];


        foreach($items as $item) {
            $name = $item['id'];

            if(!empty($item['websiteUrl'])) {
                $name .= ' - '.$item['websiteUrl'];
            } elseif(!empty($item['name'])) {
                $name .= ' - '.$item['name'];
            }

            $properties[$item['id']] = $name;
        }

        return $properties;
    }

    public function getSetting($k)
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        return $settings[$k];
    }

    public function isConfigured()
    {
        // check if plugin has finished installation process

        if(!$this->isInstalled()) {
            return false;
        }


        // check if api is available

        $api = craft()->analytics->getApiObject();

        if(!$api) {
            Craft::log(__METHOD__.' : Analytics API not available', LogLevel::Info, true);
            return false;
        }


        // check if profile id is set up

        $profileId = $this->getSetting('profileId');


        if(!$profileId) {
            Craft::log(__METHOD__.' : Analytics profileId not found', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    public function isInstalled()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // is oauth present in craft

        $oauth = craft()->plugins->getPlugin('OAuth', false);

        if(!$oauth) {
            Craft::log(__METHOD__.' : OAuth plugin files not present', LogLevel::Info, true);
            return false;
        }

        // if present, is it installed

        if(!$oauth->isInstalled) {
            Craft::log(__METHOD__.' : OAuth plugin not installed', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    /* ------------------------------------------------------------------------- */

    /**
     * Require OAuth
     */
    public function requireOAuth()
    {
        if(!isset(craft()->oauth))
        {
            throw new Exception(Craft::t('OAuth plugin is required to perform this action.'));
        }
    }

    /**
     * Get Missing Dependencies
     */
    public function getMissingDependencies()
    {
        $missingDependencies = array();
        $dependencies = $this->getDependencies();

        foreach($dependencies as $dependency)
        {
            if(!$dependency['check'])
            {
                array_push($missingDependencies, $dependency);
            }
        }

        return $missingDependencies;
    }

    /**
     * Get Dependencies
     */
    private function getDependencies()
    {
        $analytics = craft()->plugins->getPlugin('analytics');

        $dependencies = $analytics->getDependencies();

        foreach($dependencies as $key => $dependency)
        {
            $dependencies[$key] = $this->getDependency($dependency);
        }

        return $dependencies;
    }

    /**
     * Get Dependency
     */
    private function getDependency($dependency)
    {
        $check = false;
        $plugin = craft()->plugins->getPlugin($dependency['handle']);

        if($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if(version_compare($currentVersion, $dependency['version']) >= 0)
            {
                // no (requirements OK)

                $check = true;

            }
            else
            {
                // yes (requirement not OK)
            }
        }
        else
        {
            // not installed
        }

        $dependency['check'] = $check;
        $dependency['plugin'] = $plugin;

        return $dependency;
    }
}

