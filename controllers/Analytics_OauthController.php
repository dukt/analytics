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
        //  http://cl.ly/2F1O0w1P1Q1F/download/oauth-craft-0.9.zip

        if(craft()->analytics_oauth->download()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin has been downloaded.'));
        } else {
            craft()->userSession->setError(Craft::t('OAuth plugin couldn’t be downloaded.'));
        }

        $url = UrlHelper::getActionUrl('analytics/oauth/install');

        $this->redirect($url);
    }

    public function actionInstall()
    {
        if(craft()->analytics_oauth->install()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin installed.'));
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t install OAuth plugin.'));
        }

        $this->redirect('analytics/install');
    }

    public function actionEnable()
    {
        if(craft()->analytics_oauth->enable()) {
            craft()->userSession->setNotice(Craft::t('OAuth plugin enabled.'));
        } else {
            craft()->userSession->setError(Craft::t('OAuth plugin couldn’t be plugin.'));
        }

        $this->redirect('analytics/install');
    }
}