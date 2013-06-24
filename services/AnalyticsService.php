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
    public function getUpdates()
    {
        // get update from cache

        // or refresh cache and get new updates if cache expired or forced update

        $url = 'http://dukt.net/craft/analytics/releases.xml';

        $xml = simplexml_load_file($url);


        // XML from here on

        $namespaces = $xml->getNameSpaces(true);
        $versions = array();
        if (!empty($xml->channel->item)) {
            foreach ($xml->channel->item as $version) {
                $ee_addon       = $version->children($namespaces['ee_addon']);
                $version_number = (string) $ee_addon->version;
                $versions[$version_number] = $version_number;
                //var_dump($ee_addon);
            }
        }

        ksort($versions);

        $last_version = array_pop($versions);
        $current_version = craft()->plugins->getPlugin('Analytics')->getVersion();

        if($last_version > $current_version) {
            return true;
        } else {
            return false;
        }
    }

    public function code($id, $entry = NULL)
    {
        $variables = array('id' => $id, 'entry' => $entry);

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_code', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }

    public function getSetting($k) {
        $settings = Analytics_SettingsRecord::model()->find();

        if(!$settings) {
            return false;
        }

        return $settings->options[$k];
    }

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

    public function api()
    {
        $provider = craft()->oauth->getProviderLibrary('Google', 'analytics.system');

        // var_dump($provider);
        // die();

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
}

