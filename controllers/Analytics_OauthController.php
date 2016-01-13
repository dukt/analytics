<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_OauthController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $handle = 'google';

    /**
     * @var array
     */
    private $scope = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/analytics'
    );

    /**
     * @var array
     */
    private $authorizationOptions = array(
        'access_type' => 'offline',
        'approval_prompt' => 'force'
    );

    // Public Methods
    // =========================================================================

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

            AnalyticsPlugin::log('OAuth Connect Referer: '.$referer, LogLevel::Info);
        }


        // connect

        if ($response = craft()->oauth->connect(array(
            'plugin'   => 'analytics',
            'provider' => $this->handle,
            'scope'   => $this->scope,
            'authorizationOptions'   => $this->authorizationOptions
        )))
        {
            if ($response['success'])
            {
                // token
                $token = $response['token'];

                // save token
                craft()->analytics_oauth->saveToken($token);

                if($token)
                {
                    AnalyticsPlugin::log('Token: '."\r\n".print_r($token->getAttributes(), true), LogLevel::Info);
                }
                else
                {
                    AnalyticsPlugin::log('Couldn’t get token', LogLevel::Error);
                }

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

        // OAuth Step 5

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
        if (craft()->analytics_oauth->deleteToken())
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

    /**
     * Settings
     *
     * @return null
     */
    public function actionSettings()
    {
        craft()->analytics_plugin->requireDependencies();

        $provider = craft()->oauth->getProvider('google');

        if ($provider && $provider->isConfigured())
        {
            $this->redirect('analytics/settings');
        }
        else
        {
            $this->renderTemplate('analytics/_install/oauth-provider-not-configured');
        }
    }
}
