<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

class AnalyticsPlugin extends BasePlugin
{
    // Public Methods
    // =========================================================================

    /**
     * Get Name
     */
    public function getName()
    {
        return Craft::t('Analytics');
    }

    /**
     * Get Version
     */
    public function getVersion()
    {
        return '3.2.0';
    }

    /**
     * Get Required Plugins
     */
    public function getRequiredPlugins()
    {
        return array(
            array(
                'name' => "OAuth",
                'handle' => 'oauth',
                'url' => 'https://dukt.net/craft/oauth',
                'version' => '0.9.70'
            )
        );
    }

    /**
     * Get Developer
     */
    public function getDeveloper()
    {
        return 'Dukt';
    }

    /**
     * Get Developer URL
     */
    public function getDeveloperUrl()
    {
        return 'https://dukt.net/';
    }

    /**
     * Get Settings URL
     */
    public function getSettingsUrl()
    {
        return 'analytics/settings';
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return array(
            'analytics/settings' => array('action' => "analytics/settings"),
            'analytics/_special/oauth' => array('action' => "analytics/oauth/settings"),
            'analytics/_special/dependencies' => array('action' => "analytics/plugin/dependencies"),
            'analytics/meta' => array('action' => "analytics/meta/index"),
        );
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

    /**
     * Get Plugin Dependencies
     */
    public function getPluginDependencies($missingOnly = true)
    {
        $dependencies = array();

        $plugins = $this->getRequiredPlugins();

        foreach($plugins as $key => $plugin)
        {
            $dependency = $this->getPluginDependency($plugin);

            if($missingOnly)
            {
                if($dependency['isMissing'])
                {
                    $dependencies[] = $dependency;
                }
            }
            else
            {
                $dependencies[] = $dependency;
            }
        }

        return $dependencies;
    }

    /**
     * Is Configured
     */
    public function isConfigured()
    {
        // check dependencies
        $pluginDependencies = $this->getPluginDependencies();

        if(count($pluginDependencies) > 0)
        {
            return false;
        }


        // check provider and token

        $provider = craft()->oauth->getProvider('google');

        if($provider)
        {
            // token
            $token = craft()->analytics_oauth->getToken();

            if (!$token)
            {
                return false;
            }
        }
        else
        {
            return false;
        }


        // check if profile id is set up

        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        $profileId = $settings['profileId'];


        if(!$profileId)
        {
            Craft::log('Analytics profileId not found', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Defined Settings
     */
    protected function defineSettings()
    {
        return array(
            'webPropertyId' => array(AttributeType::String),
            'accountId' => array(AttributeType::String),
            'internalWebPropertyId' => array(AttributeType::String),
            'profileId' => array(AttributeType::String),
            'realtimeRefreshInterval' => array(AttributeType::Number, 'default' => 60),
            'enableRealtime' => array(AttributeType::Bool),
            'tokenId' => array(AttributeType::Number),
        );
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Plugin Dependency
     */
    private function getPluginDependency($dependency)
    {
        $isMissing = true;
        $isInstalled = true;

        $plugin = craft()->plugins->getPlugin($dependency['handle'], false);

        if($plugin)
        {
            $currentVersion = $plugin->version;


            // requires update ?

            if(version_compare($currentVersion, $dependency['version']) >= 0)
            {
                // no (requirements OK)

                if($plugin->isInstalled && $plugin->isEnabled)
                {
                    $isMissing = false;
                }
            }
            else
            {
                // yes (requirement not OK)
            }
        }
        else
        {
            // not installed
        }

        $dependency['isMissing'] = $isMissing;
        $dependency['plugin'] = $plugin;

        return $dependency;
    }
}
