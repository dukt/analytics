<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_PluginService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    /**
     * Require dependencies
     *
     * @return bool
     */
    public function requireDependencies()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
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
     * Check requirements
     *
     * @return bool
     */
    public function checkRequirements($redirect = false)
    {
        // dependencies
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
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
}
