<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsController extends BaseController
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
    private $scopes = array(
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/analytics'
    );

    /**
     * @var array
     */
    private $params = array(
        'access_type' => 'offline',
        'approval_prompt' => 'force'
    );

    // Public Methods
    // =========================================================================

    /**
     * Settings
     *
     * @return null
     */
    public function actionSettings()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->renderTemplate('analytics/settings/_dependencies', ['pluginDependencies' => $pluginDependencies]);
        }
        else
        {
            if (isset(craft()->oauth))
            {
                $variables = array(
                    'provider' => false,
                    'account' => false,
                    'token' => false,
                    'error' => false
                );

                $provider = craft()->oauth->getProvider('google');

                if ($provider && $provider->isConfigured())
                {
                    $token = craft()->analytics->getToken();

                    if ($token)
                    {
                        $provider->setToken($token);

                        try
                        {
                            $account = $provider->getAccount();

                            $propertiesOpts = craft()->analytics->getPropertiesOpts();

                            if ($account)
                            {

                                $variables['account'] = $account;
                                $variables['propertiesOpts'] =$propertiesOpts;
                                $variables['settings'] = $plugin->getSettings();
                            }
                        }
                        catch(\Exception $e)
                        {
                            Craft::log('Couldn’t get account. '.$e->getMessage(), LogLevel::Error);

                            $variables['error'] = $e->getMessage();
                        }
                    }

                    $variables['token'] = $token;

                    $variables['provider'] = $provider;
                }

                $this->renderTemplate('analytics/settings', $variables);
            }
            else
            {
                $this->renderTemplate('analytics/settings/_oauthNotInstalled');
            }
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

    /**
     * Save Widget State
     *
     * @return null
     */
    public function actionSaveWidgetState()
    {
        $widgetId = craft()->request->getPost('id');

        $formerWidget = craft()->dashboard->getUserWidgetById($widgetId);

        if($formerWidget)
        {
            $widgetSettings = craft()->request->getPost('settings');

            if(!empty($formerWidget->settings['colspan']))
            {
                $widgetSettings['colspan'] = $formerWidget->settings['colspan'];
            }

            if(empty($widgetSettings['colspan']))
            {
                $widgetSettings['colspan'] = 1;
            }

            $widget = new WidgetModel();
            $widget->id = $widgetId;
            $widget->type = 'Analytics_Explorer';
            $widget->settings = $widgetSettings;

            if (craft()->dashboard->saveUserWidget($widget))
            {
                $this->returnJson(true);
            }
            else
            {
                $this->returnErrorJson('Couldn’t save widget');
            }
        }
        else
        {
            $this->returnErrorJson('Couldn’t save widget');
        }
    }
}