<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\Plugin as Analytics;
use dukt\oauth\Plugin as Oauth;

class SettingsController extends Controller
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
		Analytics::$plugin->analytics->requireDependencies();

		$variables = array();

		$variables['isOauthProviderConfigured'] = Analytics::$plugin->analytics->isOauthProviderConfigured();

		if($variables['isOauthProviderConfigured'])
		{
			$variables['oauthAccount'] = false;
			$variables['errors'] = [];

			$provider = Oauth::$plugin->oauth->getProvider('google');
			$plugin = Craft::$app->plugins->getPlugin('analytics');
			$token = Analytics::$plugin->analytics_oauth->getToken();

			if ($token)
			{
/*				try
				{*/
					$oauthAccount = Analytics::$plugin->analytics_cache->get(['getAccount', $token]);

					if(!$oauthAccount)
					{
						$oauthAccount = $provider->getAccount($token);
						Analytics::$plugin->analytics_cache->set(['getAccount', $token], $oauthAccount);
					}

					if ($oauthAccount)
					{
                        // \dukt\analytics\Plugin::log("Account:\r\n".print_r($oauthAccount, true), LogLevel::Info);

/*                        Craft::$app->getView()->registerJsFile('analytics/js/AccountExplorer.js');
                        Craft::$app->getView()->registerCssFile('analytics/css/AccountExplorer.css');*/


						$settings = $plugin->getSettings();


                        // Account

                        $accountExplorerData = Analytics::$plugin->analytics_cache->get(['accountExplorerData']);

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
/*				}
				catch(\Google_Service_Exception $e)
				{
					// \dukt\analytics\Plugin::log("Couldn’t get OAuth account: ".$e->getMessage(), LogLevel::Error);

					foreach($e->getErrors() as $error)
					{
						array_push($variables['errors'], $error['message']);
					}
				}
				catch(\Exception $e)
				{
					if(method_exists($e, 'getResponse'))
					{
						// \dukt\analytics\Plugin::log("Couldn’t get OAuth account: ".$e->getResponse(), LogLevel::Error);
					}
					else
					{
						// \dukt\analytics\Plugin::log("Couldn’t get OAuth account: ".$e->getMessage(), LogLevel::Error);
					}

					array_push($variables['errors'], $e->getMessage());
				}*/
			}

			$variables['token'] = $token;
			$variables['provider'] = $provider;
		}
        /*Craft::$app->getView()->registerCssFile('analytics/css/settings.css');*/
        Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);
		return $this->renderTemplate('analytics/settings/_index', $variables);
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

		$pluginClass = Craft::$app->request->getRequiredBodyParam('pluginClass');
        $settings = Craft::$app->request->getBodyParam('settings');

		$plugin = Craft::$app->plugins->getPlugin($pluginClass);

		if (!$plugin)
		{
			throw new Exception(Craft::t('app', 'No plugin exists with the class “{class}”', array('class' => $pluginClass)));
		}

		$settings = Analytics::$plugin->analytics_api->populateAccountExplorerSettings($settings);

		if (Craft::$app->plugins->savePluginSettings($plugin, $settings))
		{
			Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));

			return $this->redirectToPostedUrl();
		}

		Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));

		// Send the plugin back to the template
		Craft::$app->urlManager->setRouteVariables(array(
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
            $accountExplorerData = Analytics::$plugin->analytics_api->getAccountExplorerData();

            Analytics::$plugin->analytics_cache->set(['accountExplorerData'], $accountExplorerData);

            return $this->asJson($accountExplorerData);
        }
        catch(\Exception $e)
        {
            return $this->asErrorJson($e->getMessage());
        }
    }
}