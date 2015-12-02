<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use Guzzle\Http\Client;

class Analytics_PluginService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

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
}

