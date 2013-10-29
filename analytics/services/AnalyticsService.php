<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/craft/analytics/docs#license
 * @link      http://dukt.net/craft/analytics/
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/Google_Client.php');
require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/contrib/Google_AnalyticsService.php');

use \Google_Client;
use \Google_AnalyticsService;

class AnalyticsService extends BaseApplicationComponent
{
    public function api()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $handle = 'google';
        $namespace = 'analytics.system';


        // get token

        $token = craft()->oauth->getToken($handle, $namespace);


        // provider

        $provider = craft()->oauth->getProvider($handle);

        $provider->setToken($token->getDecodedToken());

        if(!$provider) {

            Craft::log(__METHOD__.' : Could not get provider connected', LogLevel::Info, true);

            return false;
        }

        $client = new Google_Client();
        $client->setApplicationName('Google+ PHP Starter Application');
        $client->setClientId($provider->clientId);
        $client->setClientSecret($provider->clientSecret);
        $client->setRedirectUri($provider->getRedirectUri());

        $api = new Google_AnalyticsService($client);

        $providerSourceToken = $provider->getToken();
        $providerSourceToken->created = 0;
        $providerSourceToken->expires_in = $providerSourceToken->expires;
        $providerSourceToken = json_encode($providerSourceToken);

        $client->setAccessToken($providerSourceToken);

        return $api;
    }

    public function getProfile()
    {
        $webProperty = $this->getWebProperty();

        $profile = craft()->fileCache->get('analytics.profile');

        if(!$profile) {
            $profiles = $this->api()->management_profiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

            $profile = $profiles['items'][0];

            craft()->fileCache->set('analytics.profile', $profile);
        }

        return $profile;
    }

    public function getWebProperty()
    {
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

        return $webProperty;
    }


    public function isOk()
    {

        Craft::log(__METHOD__, LogLevel::Info, true);

        // we're ok to roll when we're configured, installed, and that we have a defined profileId

        $profileId = $this->getSetting('profileId');

        if($this->isConfigured() && $this->isInstalled() && $profileId)
        {
            Craft::log(__METHOD__.' : true', LogLevel::Info, true);
            return true;
        }

        Craft::log(__METHOD__.' : false. Not configured, not installed, or profileId not found', LogLevel::Info, true);

        return false;
    }


    public function properties()
    {

        Craft::log(__METHOD__, LogLevel::Info, true);

        // try {

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

            $properties = array();

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
        // } catch(\Exception $e) {

        //     Craft::log(__METHOD__.' : Crashed with error : '.$e->getMessage(), LogLevel::Info, true);

        //     return false;
        // }
    }

    public function getSetting($k)
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $settings = $plugin->getSettings();


        return $settings[$k];
    }

    public function isConfigured()
    {
        Craft::log('AnalyticsService->isConfigured()', LogLevel::Info, true);

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


        // is analytics properly installed

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

        $account = craft()->oauth->getAccount('google', 'analytics.system');

        if(!$account)
        {
            Craft::log(__METHOD__.' : Account could not be found', LogLevel::Info, true);

            return false;
        }

        return true;
    }
}

