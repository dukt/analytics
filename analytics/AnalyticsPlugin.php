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

class AnalyticsPlugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('Analytics');
    }

    function getVersion()
    {
        return '1.0.52';
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

    public function getSettingsHtml()
    {
       return craft()->templates->render('analytics/settings', array(
           'settings' => $this->getSettings()
       ));
    }
}