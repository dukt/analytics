<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/Info.php');
require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');
require_once(CRAFT_PLUGINS_PATH.'analytics/etc/AnalyticsTracking.php');

class AnalyticsPlugin extends BasePlugin
{
    // Public Methods
    // =========================================================================

    public function getName()
    {
        return Craft::t('Analytics');
    }

    public function getVersion()
    {
        return ANALYTICS_VERSION;
    }

    public function getRequiredPlugins()
    {
        return array(
            array(
                'name' => "OAuth",
                'handle' => 'oauth',
                'url' => 'https://dukt.net/craft/oauth',
                'version' => '0.9.64'
            )
        );
    }

    public function getDeveloper()
    {
        return 'Dukt';
    }

    public function getDeveloperUrl()
    {
        return 'https://dukt.net/';
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

        return craft()->templates->render('analytics/settings/_redirect', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * Hook Register CP Routes
     */
    public function registerCpRoutes()
    {
        return array(
            'analytics\/console' => array('action' => "analytics/explorer/console"),
            'analytics\/settings' => array('action' => "analytics/settings"),
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

    // Protected Methods
    // =========================================================================

    protected function defineSettings()
    {
        return array(
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