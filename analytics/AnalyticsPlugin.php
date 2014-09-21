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
require_once(CRAFT_PLUGINS_PATH.'analytics/etc/AnalyticsTracking.php');

class AnalyticsPlugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('Analytics');
    }

    function getVersion()
    {
        return '2.0.77';
    }

    function getDeveloper()
    {
        return 'Dukt';
    }

    function getDeveloperUrl()
    {
        return 'https://dukt.net/';
    }

    protected function defineSettings()
    {
        return array(
            'profileId' => array(AttributeType::String),
            'realtimeRefreshInterval' => array(AttributeType::Number),
            'tokenId' => array(AttributeType::Number),
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
        if(craft()->request->getPath() == 'settings/plugins')
        {
            return true;
        }

        return craft()->templates->render('analytics/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * On Before Uninstall
     */
    public function onBeforeUninstall()
    {
        if(isset(craft()->oauth))
        {
            craft()->oauth->deleteTokensByPlugin('analytics');
        }
    }
}