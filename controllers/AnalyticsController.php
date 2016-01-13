<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Install
     *
     * @return null
     */
    public function actionInstall()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $this->renderTemplate('analytics/_install/dependencies', ['pluginDependencies' => $pluginDependencies]);
        }
        else
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

    /**
     * Settings
     *
     * @return null
     */
    public function actionSettings()
    {
        craft()->analytics_plugin->requireDependencies();
        craft()->analytics_oauth->requireOauth();

        $plugin = craft()->plugins->getPlugin('analytics');

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
                try
                {
                    $account = craft()->analytics_cache->get(['getAccount', $token]);

                    if(!$account)
                    {
                        $account = $provider->getAccount($token);
                        craft()->analytics_cache->set(['getAccount', $token], $account);
                    }

                    $propertiesOpts = $this->getPropertiesOpts();

                    if ($account)
                    {
                        AnalyticsPlugin::log("Account:\r\n".print_r($account, true), LogLevel::Info);

                        $variables['account'] = $account;
                        $variables['propertiesOpts'] = $propertiesOpts;
                        $variables['settings'] = $plugin->getSettings();
                    }
                }
                catch(\Exception $e)
                {
                    if(method_exists($e, 'getResponse'))
                    {
                            AnalyticsPlugin::log("Couldn’t get account: ".$e->getResponse(), LogLevel::Error);
                    }
                    else
                    {
                        AnalyticsPlugin::log("Couldn’t get account: ".$e->getMessage(), LogLevel::Error);
                    }

                    $variables['error'] = $e->getMessage();
                }
            }

            $variables['token'] = $token;

            $variables['provider'] = $provider;
        }

        $this->renderTemplate('analytics/settings', $variables);
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

        $profileId = null;
        $accountId = null;
        $internalWebPropertyId = null;

        if(!empty($settings['webPropertyId']))
        {
            $webPropertyId = $settings['webPropertyId'];
            $webProperty = craft()->analytics_api->getWebProperty($webPropertyId);

            if($webProperty)
            {
                $profiles = craft()->analytics_api->getProfiles($webProperty);

                $profile = $profiles[0];

                $profileId = $profile['id'];
                $accountId = $webProperty->accountId;
                $internalWebPropertyId = $webProperty->internalWebPropertyId;
            }
        }

        $settings['profileId'] = $profileId;
        $settings['accountId'] = $accountId;
        $settings['internalWebPropertyId'] = $internalWebPropertyId;

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
