<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

class AnalyticsController extends BaseController
{
    private $handle = 'google';

    private $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/analytics'
    );

    private $params = array(
        'access_type' => 'offline',
        'approval_prompt' => 'force'
    );

    /**
     * Settings
     *
     * @return null
     */
    public function actionSettings()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        try {

            if (count($pluginDependencies) > 0)
            {
                $this->renderTemplate('analytics/settings/_dependencies', ['pluginDependencies' => $pluginDependencies]);
            }
            else
            {
                if (isset(craft()->oauth))
                {
                    $provider = craft()->oauth->getProvider('google');

                    if ($provider && $provider->isConfigured())
                    {
                        $token = craft()->analytics->getToken();

                        if ($token)
                        {
                            $provider->setToken($token);

                            $account = $provider->getAccount();

                            $propertiesOpts = craft()->analytics->getPropertiesOpts();

                            if ($account)
                            {
                                $this->renderTemplate('analytics/settings/_pluginSettings', [
                                    'account' => $account,
                                    'propertiesOpts' => $propertiesOpts,
                                    'settings' => $plugin->getSettings(),
                                ]);
                            }
                            else
                            {
                                $this->renderTemplate('analytics/settings/_connect');
                            }
                        }
                        else
                        {
                            $this->renderTemplate('analytics/settings/_connect');
                        }
                    }
                    else
                    {
                        $this->renderTemplate('analytics/settings/_providerNotConfigured');
                    }
                }
                else
                {
                    $this->renderTemplate('analytics/settings/_oauthNotInstalled');
                }
            }
        }
        catch(\Exception $e)
        {
            $this->renderTemplate('analytics/settings/_error', ['errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Connect
     *
     * @return null
     */
    public function actionConnect()
    {
        // referer

        $referer = craft()->httpSession->get('analytics.referer');

        if (!$referer)
        {
            $referer = craft()->request->getUrlReferrer();

            craft()->httpSession->add('analytics.referer', $referer);
        }


        // connect

        if ($response = craft()->oauth->connect(array(
            'plugin'   => 'analytics',
            'provider' => $this->handle,
            'scopes'   => $this->scopes,
            'params'   => $this->params
        )))
        {
            if ($response['success'])
            {
                // token
                $token = $response['token'];

                // save token
                craft()->analytics->saveToken($token);

                // session notice
                craft()->userSession->setNotice(Craft::t("Connected to Google Analytics."));
            }
            else
            {
                // session error
                craft()->userSession->setError(Craft::t($response['errorMsg']));
            }
        }
        else
        {
            // session error
            craft()->userSession->setError(Craft::t("Couldn’t connect"));
        }


        // redirect

        craft()->httpSession->remove('analytics.referer');

        $this->redirect($referer);
    }

    /**
     * Disconnect
     *
     * @return null
     */
    public function actionDisconnect()
    {
        if (craft()->analytics->deleteToken())
        {
            craft()->userSession->setNotice(Craft::t("Disconnected from Google Analytics."));
        }
        else
        {
            craft()->userSession->setError(Craft::t("Couldn’t disconnect from Google Analytics"));
        }

        // redirect
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
    }
}