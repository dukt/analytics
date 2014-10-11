<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
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

    public function saveToken($token)
    {
        // get plugin
        $plugin = craft()->plugins->getPlugin('analytics');

        // get settings
        $settings = $plugin->getSettings();

        // get tokenId
        $tokenId = $settings->tokenId;

        // get token
        $model = craft()->oauth->getTokenById($tokenId);


        // populate token model

        if(!$model)
        {
            $model = new Oauth_TokenModel;
        }

        $model->providerHandle = 'google';
        $model->pluginHandle = 'analytics';
        $model->encodedToken = craft()->oauth->encodeToken($token);

        // save token
        craft()->oauth->saveToken($model);

        // set token ID
        $settings->tokenId = $model->id;

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

            if($token && $token->token)
            {
                $this->token = $token;
                return $this->token;
            }
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

            if(craft()->config->get('disableAnalyticsCache') === true)
            {
                $enableCache = false;
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

            case 'ga:pageviewsPerVisit':
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
        $response = $this->getApiObject()->data_ga->get($p1, $p2, $p3, $p4, $p5);

        $enableCache = true;

        if(craft()->config->get('disableAnalyticsCache') === true)
        {
            $enableCache = false;
        }

        if($enableCache)
        {
            $cacheKey = 'analytics/explorer/'.md5(serialize(array($p1, $p2, $p3, $p4, $p5)));

            $return = craft()->fileCache->get($cacheKey);

            if(!$return)
            {
                $return = $this->parseApiResponse($response);

                craft()->fileCache->set($cacheKey, $return, $this->cacheDuration());
            }

            return $return;
        }
        else
        {
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

        die($cacheDuration);

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

        if(craft()->config->get('disableAnalyticsCache') === true)
        {
            $enableCache = false;
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

        if(!$provider)
        {
            Craft::log(__METHOD__.' : Could not get provider connected', LogLevel::Info, true);
            return false;
        }


        // token
        $tokenModel = craft()->analytics->getToken();

        if ($tokenModel)
        {
            $token = $tokenModel->token;

            if($token)
            {
                // make token compatible with Google library
                $arrayToken = array();
                $arrayToken['created'] = 0;
                $arrayToken['access_token'] = $token->getAccessToken();
                $arrayToken['expires_in'] = $token->getEndOfLife();
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
            Craft::log(__METHOD__.' : No token defined', LogLevel::Info, true);
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

                $webProperties = $this->getApiObject()->management_webproperties->listManagementWebproperties("~all");

                foreach($webProperties['items'] as $webPropertyItem) {

                    if($webPropertyItem['id'] == $this->getSetting('profileId')) {
                        $webProperty = $webPropertyItem;
                    }
                }

                craft()->fileCache->set('analytics.webProperty', $webProperty);
            }

            $r = $webProperty;

        } catch(\Exception $e) {
            $r['error'] = $e->getMessage();
        }

        return $r;
    }

    public function getPropertiesOpts()
    {

        $properties = array("" => "Select");

        Craft::log(__METHOD__, LogLevel::Info, true);

        try {

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
        } catch(\Exception $e) {

            Craft::log(__METHOD__.' : Crashed with error : '.$e->getMessage(), LogLevel::Info, true);

            return false;
        }
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
}

