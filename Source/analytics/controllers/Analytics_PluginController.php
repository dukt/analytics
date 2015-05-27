<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

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
     * Download
     *
     * @return null
     */
    public function actionDownload()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

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

            Craft::log(__METHOD__.' : '.$msg, LogLevel::Info, true);

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
        Craft::log(__METHOD__, LogLevel::Info, true);

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
        Craft::log(__METHOD__, LogLevel::Info, true);

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
            Craft::log(__METHOD__." : ".$pluginHandle.' plugin installed.', LogLevel::Info, true);
            craft()->userSession->setNotice(Craft::t('Plugin installed.'));
        }
        else
        {
            Craft::log(__METHOD__." : Couldn't install ".$pluginHandle." plugin.", LogLevel::Info, true);
            craft()->userSession->setError(Craft::t("Couldn't install plugin."));
        }

        // redirect
        $this->redirect(craft()->request->getUrlReferrer());
    }
}