<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/analytics/license
 */

namespace Craft;

class Analytics_PluginController extends BaseController
{
    // --------------------------------------------------------------------

    private $pluginHandle = 'analytics';
    private $referer;

    // --------------------------------------------------------------------

    public function __construct()
    {
        $this->referer = $_SERVER['HTTP_REFERER'];
    }

    // --------------------------------------------------------------------

    public function actionDownload()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginClass = craft()->request->getParam('pluginClass');
        $pluginHandle = craft()->request->getParam('pluginHandle');

        $download = craft()->analytics_plugin->download($pluginClass, $pluginHandle);

        if($download['success'] == true) {

            if(craft()->analytics_plugin->install($pluginClass)) {

                Craft::log(__METHOD__.' : '.$pluginClass.' plugin installed.', LogLevel::Info, true);

                craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));

            } else {
                Craft::log(__METHOD__.' : '.$pluginClass.' plugin not installed.', LogLevel::Info, true);

                $url = UrlHelper::getActionUrl('analytics/plugin/install',
                            array(
                                'pluginClass' => $pluginClass,
                                'pluginHandle' => $pluginHandle

                            )
                        );

                $this->redirect($url);
            }

        } else {

            $msg = 'Couldn’t install '.$pluginClass.' plugin.';

            if(isset($download['msg'])) {
                $msg = $download['msg'];
            }

            Craft::log(__METHOD__.' : '.$msg, LogLevel::Info, true);

            craft()->userSession->setError(Craft::t($msg));
        }

        $this->redirect($this->referer);
    }

    // --------------------------------------------------------------------

    public function actionInstall()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginClass = craft()->request->getParam('pluginClass');

        if(craft()->analytics_plugin->install($pluginClass)) {

            Craft::log(__METHOD__." : ".$pluginClass.' plugin installed.', LogLevel::Info, true);

            craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));
        } else {
            Craft::log(__METHOD__." : Couldn't install ".$pluginClass." plugin.", LogLevel::Info, true);

            craft()->userSession->setError(Craft::t("Couldn't install ".$pluginClass." plugin."));
        }

        $this->redirect($this->referer);
    }

    // --------------------------------------------------------------------

    public function actionUpdate()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $plugin = craft()->analytics->checkUpdatesNew();

        if($plugin) {

            Craft::log(__METHOD__." : Updates checked", LogLevel::Info, true);

            $url = UrlHelper::getActionUrl('analytics/plugin/download',
                        array(
                            'pluginClass' => $plugin['class'],
                            'pluginHandle' => $plugin['handle']

                        )
                    );

            $this->redirect($url);

        } else {
            Craft::log(__METHOD__." : Coudln't check updates", LogLevel::Info, true);

            $this->redirect('analytics/settings');
        }
    }

    // --------------------------------------------------------------------

    public function actionCheckUpdates()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $plugin = craft()->analytics->checkUpdatesNew();

        $this->returnJson($plugin);
    }

    // --------------------------------------------------------------------
}