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

class AnalyticsPlugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('Analytics');
    }

    function getVersion()
    {
        return '1.0.61';
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
            'realtimeRefreshInterval' => array(AttributeType::Number)
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