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
            craft()->userSession->setNotice(Craft::t($pluginClass.' plugin updated.'));
        } else {
            $msg = 'Couldn’t update '.$pluginClass.' plugin.';
            if(isset($download['msg'])) {
                $msg = $download['msg'];
            }
            craft()->userSession->setError(Craft::t($msg));
        }


        $this->redirect('analytics/install');
    }

    public function actionInstall()
    {
        $pluginClass = craft()->request->getParam('pluginClass');

        if(craft()->analytics_plugin->install($pluginClass))
        {
            craft()->userSession->setNotice(Craft::t($pluginClass.' plugin updated.'));
        } else {
            craft()->userSession->setError(Craft::t("Couldn't install ".$pluginClass." plugin."));
        }

        $this->redirect('analytics/install');
    }
}