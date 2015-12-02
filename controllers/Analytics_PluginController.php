<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use Guzzle\Http\Client;

class Analytics_PluginController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $pluginHandle = 'analytics';

    /**
     * @var object
     */
    private $pluginService;

    // Public Methods
    // =========================================================================

    /**
     * Constructor
     *
     * @return null
     */
    public function __construct()
    {
        $this->pluginService = craft()->{$this->pluginHandle.'_plugin'};
    }

    /**
     * Dependencies
     *
     * @return null
     */
    public function actionDependencies()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->renderTemplate('analytics/_install/dependencies', ['pluginDependencies' => $pluginDependencies]);
        }
        else
        {
            $this->redirect('analytics/settings');
        }
    }
}