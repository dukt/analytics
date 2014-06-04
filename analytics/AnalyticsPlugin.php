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

require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

class AnalyticsPlugin extends BasePlugin
{
    public function init()
    {
        craft()->on('oauth.connect', function(Event $event) {
            if(craft()->httpSession->get('oauth.plugin') == 'analytics')
            {
                // token
                $token = $event->params['token'];

                // get plugin settings
                $plugin = craft()->plugins->getPlugin('analytics');
                $settings = $plugin->getSettings();

                // save token to plugin settings
                $settings['token'] = base64_encode(serialize($token));
                craft()->plugins->savePluginSettings($plugin, $settings);

                // session notice
                craft()->userSession->setNotice(Craft::t("Connected to Google Analytics."));
            }
        });
    }

    function getName()
    {
        return Craft::t('Analytics');
    }

    function getVersion()
    {
        return '2.0.67';
    }

    function getDeveloper()
    {
        return 'Dukt';
    }

    function getDeveloperUrl()
    {
        return 'http://dukt.net/';
    }

    protected function defineSettings()
    {
        return array(
            'profileId' => array(AttributeType::String),
            'realtimeRefreshInterval' => array(AttributeType::Number),
            'token' => array(AttributeType::String),
        );
    }

    public function prepSettings($settings)
    {
        // refresh profileId and webProperty cache when settings saved

        craft()->fileCache->delete('analytics.profile');
        craft()->fileCache->delete('analytics.webProperty');

        return $settings;
    }

    public function getSettingsHtml()
    {
        if(craft()->request->getPath() == 'settings/plugins') {
            return true;
        }

        return craft()->templates->render('analytics/settings', array(
            'settings' => $this->getSettings()
        ));
    }
}