<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_SettingsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Settings Index
	 *
	 * @return null
	 */
	public function actionIndex()
	{
		craft()->analytics->requireDependencies();

		$variables = array();

		$variables['isOauthProviderConfigured'] = craft()->analytics->isOauthProviderConfigured();

		if($variables['isOauthProviderConfigured'])
		{
			$variables['oauthAccount'] = false;
			$variables['errors'] = [];

			$provider = craft()->oauth->getProvider('google');
			$plugin = craft()->plugins->getPlugin('analytics');
			$token = craft()->analytics_oauth->getToken();

			if ($token)
			{
				try
				{
					$oauthAccount = craft()->analytics_cache->get(['getAccount', $token]);

					if(!$oauthAccount)
					{
						$oauthAccount = $provider->getAccount($token);
						craft()->analytics_cache->set(['getAccount', $token], $oauthAccount);
					}

					if ($oauthAccount)
					{
                        AnalyticsPlugin::log("Account:\r\n".print_r($oauthAccount, true), LogLevel::Info);

                        craft()->templates->includeJsResource('analytics/js/AccountExplorer.js');
                        craft()->templates->includeCssResource('analytics/css/AccountExplorer.css');


						$settings = $plugin->getSettings();


                        // Account

                        $accountExplorerData = craft()->analytics_cache->get(['accountExplorerData']);

                        $accountOptions = [];

                        if(isset($accountExplorerData['accounts']))
                        {
                            foreach($accountExplorerData['accounts'] as $account)
                            {
                                $accountOptions[] = ['label' => $account['name'], 'value' => $account['id']];
                            }
                        }
                        else
                        {
                            $accountOptions[] = ['label' => $settings->accountName, 'value' => $settings->accountId];
                        }


                        // Web Properties

                        $webPropertyOptions = [];

                        if(isset($accountExplorerData['properties']))
                        {
                            foreach($accountExplorerData['properties'] as $webProperty)
                            {
                                $webPropertyOptions[] = ['label' => $webProperty['name'], 'value' => $webProperty['id']];
                            }
                        }
                        else
                        {
                            $webPropertyOptions[] = ['label' => $settings->webPropertyName, 'value' => $settings->webPropertyId];
                        }


                        // Views

                        $viewOptions = [];

                        if(isset($accountExplorerData['views']))
                        {
                            foreach($accountExplorerData['views'] as $view)
                            {
                                $viewOptions[] = ['label' => $view['name'], 'value' => $view['id']];
                            }
                        }
                        else
                        {
                            $viewOptions[] = ['label' => $settings->profileName, 'value' => $settings->profileId];
                        }

                        $variables['accountOptions'] = $accountOptions;
                        $variables['accountId'] = $settings->accountId;
                        $variables['webPropertyOptions'] = $webPropertyOptions;
                        $variables['webPropertyId'] = $settings->webPropertyId;
                        $variables['viewOptions'] = $viewOptions;
                        $variables['viewId'] = $settings->profileId;


                        $variables['accountExplorerData'] = $accountExplorerData;
                        $variables['settings'] = $settings;
                        $variables['oauthAccount'] = $oauthAccount;
					}
				}
				catch(\Google_Service_Exception $e)
				{
					AnalyticsPlugin::log("Couldn’t get OAuth account: ".$e->getMessage(), LogLevel::Error);

					foreach($e->getErrors() as $error)
					{
						array_push($variables['errors'], $error['message']);
					}
				}
				catch(\Exception $e)
				{
					if(method_exists($e, 'getResponse'))
					{
						AnalyticsPlugin::log("Couldn’t get OAuth account: ".$e->getResponse(), LogLevel::Error);
					}
					else
					{
						AnalyticsPlugin::log("Couldn’t get OAuth account: ".$e->getMessage(), LogLevel::Error);
					}

					array_push($variables['errors'], $e->getMessage());
				}
			}

			$variables['token'] = $token;
			$variables['provider'] = $provider;
		}
        craft()->templates->includeCssResource('analytics/css/settings.css');
		$this->renderTemplate('analytics/settings/_index', $variables);
	}

	/**
	 * Saves settings.
	 *
	 * @throws Exception
	 * @return null
	 */
	public function actionSaveSettings()
	{
		$this->requirePostRequest();

		$pluginClass = craft()->request->getRequiredPost('pluginClass');
        $settings = craft()->request->getPost('settings');

		$plugin = craft()->plugins->getPlugin($pluginClass);

		if (!$plugin)
		{
			throw new Exception(Craft::t('No plugin exists with the class “{class}”', array('class' => $pluginClass)));
		}

		$settings = craft()->analytics_api->populateAccountExplorerSettings($settings);

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
     * Get Account Explorer Data
     *
     * @return null
     */
    public function actionGetAccountExplorerData()
    {
        try
        {
            $accountExplorerData = craft()->analytics_api->getAccountExplorerData();

            craft()->analytics_cache->set(['accountExplorerData'], $accountExplorerData);

            $this->returnJson($accountExplorerData);
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}