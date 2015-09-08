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

    public function actionDependencies()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->renderTemplate('analytics/_special/dependencies', ['pluginDependencies' => $pluginDependencies]);
        }
        else
        {
            $this->redirect('analytics/settings');
        }
    }

    /**
     * Download
     *
     * @return null
     */
    public function actionDownload()
    {
        $pluginHandle = craft()->request->getParam('plugin');


        // download plugin (includes download, unzip)

        $download = $this->pluginService->download($pluginHandle);

        if($download['success'] == true)
        {
            $this->redirect(
                UrlHelper::getActionUrl(
                    $this->pluginHandle.'/plugin/install',
                    array('plugin' => $pluginHandle, 'redirect' => craft()->request->getUrlReferrer())
                )
            );
        }
        else
        {
            // download failure

            $msg = 'Couldn’t install plugin.';

            if(isset($download['msg']))
            {
                $msg = $download['msg'];
            }

            Craft::log($msg, LogLevel::Error);

            craft()->userSession->setError(Craft::t($msg));
        }

        // redirect
        $this->redirect(craft()->request->getUrlReferrer());
    }

    /**
     * Enable
     *
     * @return null
     */
    public function actionEnable()
    {
        $pluginHandle = craft()->request->getParam('plugin');

        $this->pluginService->enable($pluginHandle);

        $this->redirect(craft()->request->getUrlReferrer());
    }

    /**
     * Install
     *
     * @return null
     */
    public function actionInstall()
    {
        // pluginHandle

        $pluginHandle = craft()->request->getParam('plugin');
        $redirect = craft()->request->getParam('redirect');

        if (!$redirect)
        {
            $redirect = craft()->request->getUrlReferrer();
        }


        // install plugin

        if($this->pluginService->install($pluginHandle))
        {
            Craft::log($pluginHandle.' plugin installed.', LogLevel::Info);
            craft()->userSession->setNotice(Craft::t('Plugin installed.'));
        }
        else
        {
            Craft::log("Couldn't install ".$pluginHandle." plugin.", LogLevel::Error);
            craft()->userSession->setError(Craft::t("Couldn't install plugin."));
        }

        // redirect
        $this->redirect(craft()->request->getUrlReferrer());
    }
}