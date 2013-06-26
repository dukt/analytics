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

    // --------------------------------------------------------------------

    public function actionDownload()
    {
        $pluginClass = craft()->request->getParam('pluginClass');
        $pluginHandle = craft()->request->getParam('pluginHandle');

        $download = craft()->analytics_plugin->download($pluginClass, $pluginHandle);

        if($download['success'] == true) {

            if(craft()->analytics_plugin->install($pluginClass)) {

                craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));

            } else {

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
            craft()->userSession->setError(Craft::t($msg));
        }

        $referer = $_SERVER['HTTP_REFERER'];

        $this->redirect($referer);
    }

    public function actionInstall()
    {
        $pluginClass = craft()->request->getParam('pluginClass');

        if(craft()->analytics_plugin->install($pluginClass))
        {
            craft()->userSession->setNotice(Craft::t($pluginClass.' plugin installed.'));
        } else {
            craft()->userSession->setError(Craft::t("Couldn't install ".$pluginClass." plugin."));
        }


        $referer = $_SERVER['HTTP_REFERER'];

        $this->redirect($referer);
    }

    public function actionUpdate()
    {
        $plugin = craft()->analytics->checkUpdatesNew();

        if($plugin) {

            $url = UrlHelper::getActionUrl('analytics/plugin/download',
                        array(
                            'pluginClass' => $plugin['class'],
                            'pluginHandle' => $plugin['handle']

                        )
                    );

            $this->redirect($url);

        } else {
            $this->redirect('analytics/settings');
        }
    }

    public function actionCheckUpdates()
    {
        $plugin = craft()->analytics->checkUpdatesNew();

        $this->returnJson($plugin);
    }
}