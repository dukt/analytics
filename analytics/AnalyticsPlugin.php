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
    /**
     * Get Name
     */
    function getName()
    {
        return Craft::t('Analytics');
    }

    // --------------------------------------------------------------------

    /**
     * Get Version
     */
    function getVersion()
    {
        return '0.9.45';
    }

    // --------------------------------------------------------------------

    /**
     * Get Developer
     */
    function getDeveloper()
    {
        return 'Dukt';
    }

    // --------------------------------------------------------------------

    /**
     * Get Developer URL
     */
    function getDeveloperUrl()
    {
        return 'http://dukt.net/';
    }

    // --------------------------------------------------------------------

    /**
     * Has CP Section
     */
    public function hasCpSection()
    {
        return false;
    }

    protected function defineSettings()
    {
        return array(
            'profileId' => array(AttributeType::String),
        );
    }

    public function hookRegisterCpRoutes()
    {
        return array(
            'analytics\/install\/(?P<page>.*)' => 'analytics/install/index',
        );
    }


    public function getSettingsHtml()
    {
       return craft()->templates->render('analytics/settings', array(
           'settings' => $this->getSettings()
       ));
    }
}