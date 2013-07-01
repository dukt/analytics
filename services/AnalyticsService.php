<?php

/**
 * Craft Analytics
 *
 * @package     Craft Analytics
 * @version     Version 1.0
 * @author      Benjamin David
 * @copyright   Copyright (c) 2013 - DUKT
 * @link        http://dukt.net/add-ons/craft/analytics/
 *
 */

namespace Craft;


require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/Google_Client.php');
require_once(CRAFT_PLUGINS_PATH.'analytics/libraries/google-api-php-client/src/contrib/Google_AnalyticsService.php');

use \Google_Client;
use \Google_AnalyticsService;

class AnalyticsService extends BaseApplicationComponent
{

    // --------------------------------------------------------------------

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

        $profileId = craft()->analytics->getSetting('profileId');

        if(!$profileId) {
            Craft::log(__METHOD__.' : Analytics profileId not found', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

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

        // dummy call to GA API, if it works then we are connected

        $props = $this->properties();

        if(!$props)
        {
            Craft::log(__METHOD__.' : Properties could not be found', LogLevel::Info, true);

            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    public function isOk()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // we're ok to roll when we're configured, installed, and that we have a defined profileId

        $profileId = craft()->analytics->getSetting('profileId');

        if($this->isConfigured() && $this->isInstalled() && $profileId)
        {
            Craft::log(__METHOD__.' : true', LogLevel::Info, true);
            return true;
        }

        Craft::log(__METHOD__.' : false. Not configured, not installed, or profileId not found', LogLevel::Info, true);

        return false;
    }

    // --------------------------------------------------------------------

    public function code()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $element = craft()->urlManager->getMatchedElement();

        $profileId = craft()->analytics->getSetting('profileId');

        $variables = array('id' => $profileId, 'element' => $element);

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_code', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }

    // --------------------------------------------------------------------

    public function trackEvent($category, $action, $label=null, $value=0)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $r = "
            var el=this;
        ";

        $r .= "
            ga('send', 'event', '".$category."', '".$action."', '".$label."', ".$value.");



            setTimeout(function() {
                location.href = el.href;
            }, 100);

            return false;
            ";

        return $r;
    }

    // --------------------------------------------------------------------
    // --------------------------------------------------------------------

    public function api()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $provider = craft()->oauth->getProviderLibrary('Google', 'analytics.system');

        if(!$provider) {

            Craft::log(__METHOD__.' : Could not get provider library', LogLevel::Info, true);

            return false;
        }

        $client = new Google_Client();


        $client->setApplicationName('Google+ PHP Starter Application');

        $client->setClientId($provider->client_id);
        $client->setClientSecret($provider->client_secret);
        $client->setRedirectUri($provider->redirect_uri);

        $api = new Google_AnalyticsService($client);


        $provider->token->created = 0;
        $provider->token->expires_in = $provider->token->expires;
        $provider->token = json_encode($provider->token);

        $client->setAccessToken($provider->token);

        return $api;
    }

    // --------------------------------------------------------------------

    public function checkUpdates($pluginClass, $pluginHandle)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $last = craft()->analytics_plugin->getLastVersion($pluginClass, $pluginHandle);

        $currentPlugin = craft()->plugins->getPlugin($pluginClass);

        if(!$currentPlugin) {
            return $last_version;
        }

        $current_version = $currentPlugin->getVersion();
        $remoteVersion = trim((string) $last['addon']->version);

        if($remoteVersion > $current_version) {
            // there is an update available
            Craft::log(__METHOD__.' : Update available ', LogLevel::Info, true);
            return true;
        } else {
            Craft::log(__METHOD__.' : No update available ', LogLevel::Info, true);

            return false;
        }
    }

    // --------------------------------------------------------------------

    public function checkUpdatesNew()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // check analytics updates

        $plugin = array(
                'class' => "Analytics",
                'handle' => 'analytics'
            );

        $updates = craft()->analytics->checkUpdates($plugin['class'], $plugin['handle']);

        if($updates) {
            return $plugin;
        }


        // check oauth updates

        $plugin = array(
                'class' => "Oauth",
                'handle' => 'oauth'
            );

        $updates = craft()->analytics->checkUpdates($plugin['class'], $plugin['handle']);

        if($updates) {
            return $plugin;
        }

        return false;
    }

    // --------------------------------------------------------------------

    public function getSetting($k)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $settings = Analytics_SettingsRecord::model()->find();

        if(!$settings) {
            Craft::log(__METHOD__.' : Setting not found', LogLevel::Info, true);

            return false;
        }

        return $settings->options[$k];
    }

    // --------------------------------------------------------------------

    public function properties()
    {
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

            $properties = array();

            foreach($items as $item) {
                $properties[$item['id']] = '('.$item['id'].') '.$item['websiteUrl'];
            }

            return $properties;
        } catch(\Exception $e) {

            Craft::log(__METHOD__.' : Crashed with error : '.$e->getMessage(), LogLevel::Info, true);

            return false;
        }
    }

    // --------------------------------------------------------------------
}

