<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsBehavior extends BaseBehavior
{
    // Public Methods
    // =========================================================================

    /**
     * Is Configured
     */
    public function isConfigured()
    {
        // check dependencies
        $missingPlugins = $this->getMissingDependencies();

        if(count($missingPlugins) > 0)
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
            AnalyticsPlugin::log('Analytics profileId not found', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    /**
     * Require dependencies
     *
     * @return bool
     */
    public function requireDependencies()
    {
        $missingDependencies = $this->getMissingDependencies();

        if (count($missingDependencies) > 0)
        {
            $url = UrlHelper::getUrl('analytics/install');
            craft()->request->redirect($url);
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Check Dependencies
     */
    public function checkDependencies()
    {
        $missingDependencies = $this->getMissingDependencies();

        if(count($missingDependencies) > 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Check requirements
     *
     * @return bool
     */
    public function checkRequirements($redirect = false)
    {
        if(!$this->checkDependencies())
        {
            if($redirect)
            {
                $url = UrlHelper::getUrl('analytics/install');
                craft()->request->redirect($url);
            }

            return false;
        }
        else
        {
            // oauth
            $provider = craft()->oauth->getProvider('google');

            if ($provider && $provider->isConfigured())
            {
                $token = craft()->analytics_oauth->getToken();

                if($token)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                if($redirect)
                {
                    $url = UrlHelper::getUrl('analytics/install');
                    craft()->request->redirect($url);
                }

                return false;
            }
        }
    }

    /**
     * Get Missing Dependencies
     */
    public function getMissingDependencies()
    {
        return $this->getDependencies(true);
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Plugin Dependencies
     */
    private function getDependencies($missingOnly = false)
    {
        $dependencies = array();

        $plugin = craft()->plugins->getPlugin('analytics');
        $plugins = $plugin->getRequiredPlugins();

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
        $dependency['pluginLink'] = 'https://dukt.net/craft/'.$dependency['handle'];

        return $dependency;
    }
}
