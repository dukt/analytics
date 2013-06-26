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

    public function code()
    {
        $element = craft()->urlManager->getMatchedElement();

        $profileId = craft()->analytics->getSetting('profileId');

        // $variables = array('id' => $profileId, 'entry' => $entry);

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
        $provider = craft()->oauth->getProviderLibrary('Google', 'analytics.system');

        if(!$provider) {
            return false;
        }

        $client = new Google_Client();


        $client->setApplicationName('Google+ PHP Starter Application');

        $client->setClientId($provider->client_id);
        $client->setClientSecret($provider->client_secret);
        $client->setRedirectUri($provider->redirect_uri);
        //$client->setDeveloperKey('AIzaSyA0pR4R2Pp2Ku5IKDoYoPC0Bay-1cGpee4');
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
        $last = craft()->analytics_plugin->getLastVersion($pluginClass, $pluginHandle);

        $currentPlugin = craft()->plugins->getPlugin($pluginClass);

        if(!$currentPlugin) {
            return $last_version;
        }

        $current_version = $currentPlugin->getVersion();

        if($last['addon']->version > $current_version) {

            // there is an update available

            return true;
        } else {
            return false;
        }
    }

    public function checkUpdatesNew()
    {
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
        $settings = Analytics_SettingsRecord::model()->find();

        if(!$settings) {
            return false;
        }

        return $settings->options[$k];
    }

    // --------------------------------------------------------------------

    public function isConfigured()
    {
        // check if plugin has finished installation process

        if(!$this->isInstalled()) {
            return false;
        }


        // check if api is available

        $api = craft()->analytics->api();

        if(!$api) {
            return false;
        }


        // is analytics properly installed

        $profileId = craft()->analytics->getSetting('profileId');

        if(!$profileId) {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    public function isInstalled()
    {
        // is oauth installed

        $oauth = craft()->plugins->getPlugin('OAuth', false);

        if(!$oauth) {
            return false;
        }

        if(!$oauth->isInstalled) {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------

    public function properties()
    {
        $response = craft()->analytics->api()->management_webproperties->listManagementWebproperties("~all");

        $items = $response['items'];

        $properties = array();

        foreach($items as $item) {
            $properties[$item['id']] = '('.$item['id'].') '.$item['websiteUrl'];
        }

        return $properties;
    }

    // --------------------------------------------------------------------
}

