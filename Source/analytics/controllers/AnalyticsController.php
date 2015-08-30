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
                    $token = craft()->analytics_oauth->getToken();

                    if ($token)
                    {
                        $provider->setToken($token);

                        try
                        {
                            $account = $provider->getAccount();

                            $propertiesOpts = $this->getPropertiesOpts();

                            if ($account)
                            {

                                $variables['account'] = $account;
                                $variables['propertiesOpts'] =$propertiesOpts;
                                $variables['settings'] = $plugin->getSettings();
                            }
                        }
                        catch(\Exception $e)
                        {

                            Craft::log("Analytics.Debug - Couldn't get account\r\n".$e->getMessage().'\r\n'.$e->getTraceAsString(), LogLevel::Info, true);

                            if(method_exists($e, 'getResponse'))
                            {
                                    Craft::log("Videos.Debug.GuzzleErrorResponse\r\n".$e->getResponse(), LogLevel::Info, true);
                            }

                            // throw $e;

                            // Craft::log('Couldn’t get account. '.$e->getMessage(), LogLevel::Error);

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
     * Saves a plugin's settings.
     *
     * @throws Exception
     * @return null
     */
    public function actionSavePluginSettings()
    {
        $this->requirePostRequest();
        $pluginClass = craft()->request->getRequiredPost('pluginClass');
        $settings = craft()->request->getPost('settings');

        $plugin = craft()->plugins->getPlugin($pluginClass);

        if (!$plugin)
        {
            throw new Exception(Craft::t('No plugin exists with the class “{class}”', array('class' => $pluginClass)));
        }


        if(!empty($settings['webPropertyId']))
        {
            $webPropertyId = $settings['webPropertyId'];
            $webProperty = craft()->analytics_api->getWebProperty($webPropertyId);

            if($webProperty)
            {
                $profiles = craft()->analytics_api->getProfiles($webProperty);

                $profile = $profiles[0];

                $settings['profileId'] = $profile['id'];
                $settings['accountId'] = $webProperty->accountId;
                $settings['internalWebPropertyId'] = $webProperty->internalWebPropertyId;
            }
        }

        // var_dump($webProperty);

        // var_dump($settings);
        // die();

        if (craft()->plugins->savePluginSettings($plugin, $settings))
        {
            craft()->userSession->setNotice(Craft::t('Plugin settings saved.'));

            $this->redirectToPostedUrl();
        }

        craft()->userSession->setError(Craft::t('Couldn’t save plugin settings.'));

        // Send the plugin back to the template
        craft()->urlManager->setRouteVariables(array(
            'plugin' => $plugin
        ));
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

        Craft::log('Analytics Connect - Step 1'."\r\n".print_r([
                'referer' => $referer,
            ], true), LogLevel::Info, true);


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
                craft()->analytics_oauth->saveToken($token);

                Craft::log('Analytics Connect - Step 2'."\r\n".print_r([
                        'token' => $token,
                    ], true), LogLevel::Info, true);

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
            $postSettings = craft()->request->getPost('settings');

            $widgetSettings = [
                'colspan' => $postSettings['colspan'],
                'chart' => $postSettings['chart'],
                'period' => $postSettings['period'],
            ];

            if(isset($postSettings['options']))
            {
                $widgetSettings['options'] = $postSettings['options'];
            }

            $widget = new WidgetModel();
            $widget->id = $widgetId;
            $widget->type = $formerWidget->type;
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

    /**
     * Settings Modal
     *
     * @return null
     */
    public function actionSettingsModal()
    {
        $widgetId = craft()->request->getPost('id');
        $widget = craft()->dashboard->getUserWidgetById($widgetId);

        $dataSource = craft()->analytics->getDataSource();
        $inject = $dataSource->getSettingsHtml([
            'settings' => $widget->settings,
        ]);

        $response['html'] = craft()->templates->render('analytics/widgets/stats/settingsModal', array(
            'settings' => $widget->settings,
            'inject' => $inject,
        ));

        $this->returnJson($response);
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Properties Opts
     */
    private function getPropertiesOpts()
    {
        $properties = array("" => Craft::t("Select"));

        $items = craft()->analytics_api->getWebProperties();

        foreach($items as $item)
        {
            $name = $item['id'];

            if(!empty($item['websiteUrl']))
            {
                $name .= ' - '.$item['websiteUrl'];
            }
            elseif(!empty($item['name']))
            {
                $name .= ' - '.$item['name'];
            }

            $properties[$item['id']] = $name;
        }

        return $properties;
    }

}
