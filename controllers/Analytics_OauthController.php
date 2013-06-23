<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/directory
 */

namespace Craft;

class Analytics_OauthController extends BaseController
{
    public function actionDownload()
    {
        $key = craft()->request->getParam('key');

        if(craft()->directory->download($key)) {
            $redirect = UrlHelper::getActionUrl('directory/plugin/install', array('key' => $key));

            $this->redirect($redirect);
        } else {
            $this->redirect('directory');
        }
    }

    public function actionInstall()
    {
        $class = 'OAuth';

        $pluginComponent = craft()->plugins->getPlugin($class, false);

        try {
            if(!$pluginComponent->isInstalled) {
                if (craft()->plugins->installPlugin($class)) {
                    craft()->userSession->setNotice(Craft::t('Plugin installed.'));
                } else {
                    craft()->userSession->setError(Craft::t('Couldn’t install plugin.'));
                }
            } else {
                craft()->userSession->setNotice(Craft::t('Plugin installed.'));
            }
        } catch(\Exception $e) {
            craft()->userSession->setError(Craft::t('Couldn’t install plugin.'));
        }

        $this->redirect('analytics/settings');
    }

    public function actionEnable()
    {
        $class = 'OAuth';

        $pluginComponent = craft()->plugins->getPlugin($class, false);

        try {
            if(!$pluginComponent->isEnabled) {
                if (craft()->plugins->enablePlugin($class)) {
                    craft()->userSession->setNotice(Craft::t('Plugin enabled.'));
                } else {
                    craft()->userSession->setError(Craft::t('Couldn’t enable plugin.'));
                }
            } else {
                craft()->userSession->setNotice(Craft::t('Plugin enabled.'));
            }
        } catch(\Exception $e) {
            craft()->userSession->setError(Craft::t('Couldn’t enable plugin.'));
        }

        $this->redirect('analytics/settings');
    }
}