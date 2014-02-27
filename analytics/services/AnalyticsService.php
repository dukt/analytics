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

require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/Google_Client.php');
require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/contrib/Google_AnalyticsService.php');

use \Google_Client;
use \Google_AnalyticsService;

class AnalyticsService extends BaseApplicationComponent
{
    public function getChartFromData($data)
    {
        $query = $data['query'];

        $result = craft()->analytics->api()->data_ga->get(
            $query['param1'],
            $query['param2'],
            $query['param3'],
            $query['param4'],
            $query['param5']
        );

        $rows = array();

        // foreach($result['rows'] as $v)
        // {
        //     $row = array($v[0], (int) $v[1]);
        //     $rows[] = $row;
        // }

        foreach($result['rows'] as $v) {
            $itemMetric = (int) array_pop($v);
            $itemDimension = implode('.', $v);

            if($itemDimension != "(not provided)" && $itemDimension != "(not set)") {
                $item = array($itemDimension, $itemMetric);
                array_push($rows, $item);
            }
        }

        $data['rows'] = $rows;

        return $data;
    }

    public function api()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $handle = 'google';
        $namespace = 'analytics.system';


        // get token

        $token = craft()->oauth->getSystemToken($handle, $namespace);


        // provider

        $provider = craft()->oauth->getProvider($handle);

        if(!$provider) {

            Craft::log(__METHOD__.' : Could not get provider connected', LogLevel::Info, true);

            return false;
        }

        if(!$token) {

            Craft::log(__METHOD__.' : No token defined', LogLevel::Info, true);

            return false;
        }


        // init api

        $client = new Google_Client();
        $client->setApplicationName('Google+ PHP Starter Application');
        $client->setClientId($provider->clientId);
        $client->setClientSecret($provider->clientSecret);
        $client->setRedirectUri($provider->getRedirectUri());

        $api = new Google_AnalyticsService($client);

        $realToken = $token->getRealToken();
        $realToken->created = 0;
        $realToken->expires_in = $realToken->expires;
        $realToken = json_encode($realToken);

        $client->setAccessToken($realToken);

        return $api;
    }

    public function getAccount()
    {

        try {
            return @craft()->oauth->getAccount('google', 'analytics.system');
        } catch(\Exception $e) {
            return false;
        }
    }

    public function getProfile()
    {
        $r = array();

        try {
            $webProperty = $this->getWebProperty();

            $profile = craft()->fileCache->get('analytics.profile');

            if(!$profile && !empty($webProperty['accountId'])) {
                $profiles = $this->api()->management_profiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

                $profile = $profiles['items'][0];

                craft()->fileCache->set('analytics.profile', $profile);
            }

            if($profile) {
                $r = $profile;
            } else {
                $r['error'] = "Couldn't get profile";
            }



        } catch(\Exception $e) {
            $r['error'] = $e->getMessage();
        }

        return $r;
    }

    public function getWebProperty()
    {

        $r = array();

        try {

            $webProperty = craft()->fileCache->get('analytics.webProperty');

            if(!$webProperty) {

                $webProperties = $this->api()->management_webproperties->listManagementWebproperties("~all");

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

    public function properties()
    {

        $properties = array("" => "Select");

        Craft::log(__METHOD__, LogLevel::Info, true);

        try {

            $api = craft()->analytics->api();

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

        $api = craft()->analytics->api();

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


        // try to get an account

        $token = craft()->oauth->getSystemToken('google', 'analytics.system');

        if(!$token)
        {
            Craft::log(__METHOD__." : Couldn't find a valid token", LogLevel::Info, true);

            return false;
        }

        return true;
    }
}

