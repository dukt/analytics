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

						$variables['oauthAccount'] = $oauthAccount;
						$variables['settings'] = $plugin->getSettings();
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

		$this->renderTemplate('analytics/settings', $variables);
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

            $this->returnJson($accountExplorerData);
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}