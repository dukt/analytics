<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\web\assets\settings\SettingsAsset;
use dukt\analytics\Plugin as Analytics;

class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Settings Index
     *
     * @return null
     */
    public function actionIndex($plugin = null)
    {
        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        if($isOauthProviderConfigured) {
            $errors = [];


            try {
                if(!$plugin) {
                    $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
                }

                $provider = Analytics::$plugin->oauth->getOauthProvider();
                $token = Analytics::$plugin->oauth->getToken();

                if ($token) {
                    $oauthAccount = Analytics::$plugin->cache->get(['getAccount', $token]);

                    if (!$oauthAccount) {
                        $oauthAccount = $provider->getResourceOwner($token);
                        Analytics::$plugin->cache->set(['getAccount', $token], $oauthAccount);
                    }

                    if ($oauthAccount) {
                        Craft::info("Account:\r\n".print_r($oauthAccount, true), __METHOD__);

                        $settings = $plugin->getSettings();


                        // Account

                        $accountExplorerData = Analytics::$plugin->cache->get(['accountExplorerData']);

                        $accountOptions = [];

                        if (isset($accountExplorerData['accounts'])) {
                            foreach ($accountExplorerData['accounts'] as $account) {
                                $accountOptions[] = ['label' => $account['name'], 'value' => $account['id']];
                            }
                        } else {
                            $accountOptions[] = ['label' => $settings->accountName, 'value' => $settings->accountId];
                        }


                        // Web Properties

                        $webPropertyOptions = [];

                        if (isset($accountExplorerData['properties'])) {
                            foreach ($accountExplorerData['properties'] as $webProperty) {
                                $webPropertyOptions[] = ['label' => $webProperty['name'], 'value' => $webProperty['id']];
                            }
                        } else {
                            $webPropertyOptions[] = ['label' => $settings->webPropertyName, 'value' => $settings->webPropertyId];
                        }


                        // Views

                        $viewOptions = [];

                        if (isset($accountExplorerData['views'])) {
                            foreach ($accountExplorerData['views'] as $view) {
                                $viewOptions[] = ['label' => $view['name'], 'value' => $view['id']];
                            }
                        } else {
                            $viewOptions[] = ['label' => $settings->profileName, 'value' => $settings->profileId];
                        }

                        $accountId = $settings->accountId;
                        $webPropertyId = $settings->webPropertyId;
                        $viewId = $settings->profileId;
                    }
                }
            } catch (\Google_Service_Exception $e) {
                Craft::info("Couldn’t get OAuth account: ".$e->getMessage(), __METHOD__);

                foreach ($e->getErrors() as $error) {
                    array_push($errors, $error['message']);
                }
            } catch (\Exception $e) {
                if (method_exists($e, 'getResponse')) {
                    Craft::info("Couldn’t get OAuth account: ".$e->getResponse(), __METHOD__);
                } else {
                    Craft::info("Couldn’t get OAuth account: ".$e->getMessage(), __METHOD__);
                }

                array_push($errors, $e->getMessage());
            }
        }

        $token = (isset($token) ? $token : null);

        Craft::$app->getView()->registerAssetBundle(SettingsAsset::class);

        return $this->renderTemplate('analytics/settings/_index', [
            'isOauthProviderConfigured' => $isOauthProviderConfigured,

            'accountExplorerData' => (isset($accountExplorerData) ? $accountExplorerData : null),
            'accountId' => (isset($accountId) ? $accountId : null),
            'accountOptions' => (isset($accountOptions) ? $accountOptions : null),
            'errors' => (isset($errors) ? $errors : null),
            'oauthAccount' => (isset($oauthAccount) ? $oauthAccount : null),
            'provider' => (isset($provider) ? $provider : null),
            'settings' => (isset($settings) ? $settings : null),
            'token' => (isset($token) ? $token : null),
            'viewId' => (isset($viewId) ? $viewId : null),
            'viewOptions' => (isset($viewOptions) ? $viewOptions : null),
            'webPropertyId' => (isset($webPropertyId) ? $webPropertyId : null),
            'webPropertyOptions' => (isset($webPropertyOptions) ? $webPropertyOptions : null),

            'javascriptOrigin' => Analytics::$plugin->oauth->getJavascriptOrigin(),
            'redirectUri' => Analytics::$plugin->oauth->getRedirectUri(),
            'googleIconUrl' => Craft::$app->assetManager->getPublishedUrl('@dukt/analytics/icons/google.svg', true),
        ]);
    }

    /**
     * OAuth Settings
     *
     * @return \yii\web\Response
     */
    public function actionOauth()
    {
        return $this->renderTemplate('analytics/settings/_oauth', [
            'javascriptOrigin' => Analytics::$plugin->oauth->getJavascriptOrigin(),
            'redirectUri' => Analytics::$plugin->oauth->getRedirectUri(),
            'googleIconUrl' => Craft::$app->assetManager->getPublishedUrl('@dukt/analytics/icons/google.svg', true),
            'settings' => Analytics::$plugin->getSettings(),
        ]);
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

        $pluginClass = Craft::$app->getRequest()->getRequiredBodyParam('pluginClass');
        $settings = Craft::$app->getRequest()->getBodyParam('settings');

        $plugin = Craft::$app->getPlugins()->getPlugin($pluginClass);

        if (!$plugin)
        {
            throw new Exception(Craft::t('analytics', 'No plugin exists with the class “{class}”', array('class' => $pluginClass)));
        }

        $settings = Analytics::$plugin->getApis()->getAnalytics()->populateAccountExplorerSettings($settings);

        if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settings))
        {
            Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Plugin settings saved.'));

            return $this->redirectToPostedUrl();
        }

        Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save plugin settings.'));

        // Send the plugin back to the template
        Craft::$app->getUrlManager()->setRouteParams([
            'plugin' => $plugin
        ]);

        return null;
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
            $accountExplorerData = Analytics::$plugin->getApis()->getAnalytics()->getAccountExplorerData();

            Analytics::$plugin->cache->set(['accountExplorerData'], $accountExplorerData);

            return $this->asJson($accountExplorerData);
        }
        catch(\Exception $e)
        {
            return $this->asErrorJson($e->getMessage());
        }
    }

    public function actionSites()
    {
        $sites = Craft::$app->getSites()->getAllSites();
        
        return $this->renderTemplate('analytics/settings/sites/_index', [
            'sites' => $sites
        ]);
    }

    public function actionEditSite($siteId)
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);

        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        if($isOauthProviderConfigured) {
            $errors = [];


            try {
                $plugin = Craft::$app->getPlugins()->getPlugin('analytics');

                $provider = Analytics::$plugin->oauth->getOauthProvider();
                $token = Analytics::$plugin->oauth->getToken();

                if ($token) {
                    $oauthAccount = Analytics::$plugin->cache->get(['getAccount', $token]);

                    if (!$oauthAccount) {
                        $oauthAccount = $provider->getResourceOwner($token);
                        Analytics::$plugin->cache->set(['getAccount', $token], $oauthAccount);
                    }

                    if ($oauthAccount) {
                        Craft::info("Account:\r\n".print_r($oauthAccount, true), __METHOD__);

                        $settings = $plugin->getSettings();


                        // Account

                        $accountExplorerData = Analytics::$plugin->cache->get(['accountExplorerData']);

                        $accountOptions = [];

                        if (isset($accountExplorerData['accounts'])) {
                            foreach ($accountExplorerData['accounts'] as $account) {
                                $accountOptions[] = ['label' => $account['name'], 'value' => $account['id']];
                            }
                        } else {
                            $accountOptions[] = ['label' => $settings->accountName, 'value' => $settings->accountId];
                        }


                        // Web Properties

                        $webPropertyOptions = [];

                        if (isset($accountExplorerData['properties'])) {
                            foreach ($accountExplorerData['properties'] as $webProperty) {
                                $webPropertyOptions[] = ['label' => $webProperty['name'], 'value' => $webProperty['id']];
                            }
                        } else {
                            $webPropertyOptions[] = ['label' => $settings->webPropertyName, 'value' => $settings->webPropertyId];
                        }


                        // Views

                        $viewOptions = [];

                        if (isset($accountExplorerData['views'])) {
                            foreach ($accountExplorerData['views'] as $view) {
                                $viewOptions[] = ['label' => $view['name'], 'value' => $view['id']];
                            }
                        } else {
                            $viewOptions[] = ['label' => $settings->profileName, 'value' => $settings->profileId];
                        }

                        $accountId = $settings->accountId;
                        $webPropertyId = $settings->webPropertyId;
                        $viewId = $settings->profileId;
                    }
                }
            } catch (\Google_Service_Exception $e) {
                Craft::info("Couldn’t get OAuth account: ".$e->getMessage(), __METHOD__);

                foreach ($e->getErrors() as $error) {
                    array_push($errors, $error['message']);
                }
            } catch (\Exception $e) {
                if (method_exists($e, 'getResponse')) {
                    Craft::info("Couldn’t get OAuth account: ".$e->getResponse(), __METHOD__);
                } else {
                    Craft::info("Couldn’t get OAuth account: ".$e->getMessage(), __METHOD__);
                }

                array_push($errors, $e->getMessage());
            }
        }

        $token = (isset($token) ? $token : null);

        Craft::$app->getView()->registerAssetBundle(SettingsAsset::class);

        return $this->renderTemplate('analytics/settings/sites/_edit', [
            'site' => $site,

            'isOauthProviderConfigured' => $isOauthProviderConfigured,

            'accountExplorerData' => (isset($accountExplorerData) ? $accountExplorerData : null),
            'accountId' => (isset($accountId) ? $accountId : null),
            'accountOptions' => (isset($accountOptions) ? $accountOptions : null),
            'errors' => (isset($errors) ? $errors : null),
            'oauthAccount' => (isset($oauthAccount) ? $oauthAccount : null),
            'provider' => (isset($provider) ? $provider : null),
            'settings' => (isset($settings) ? $settings : null),
            'token' => (isset($token) ? $token : null),
            'viewId' => (isset($viewId) ? $viewId : null),
            'viewOptions' => (isset($viewOptions) ? $viewOptions : null),
            'webPropertyId' => (isset($webPropertyId) ? $webPropertyId : null),
            'webPropertyOptions' => (isset($webPropertyOptions) ? $webPropertyOptions : null),

            'javascriptOrigin' => Analytics::$plugin->oauth->getJavascriptOrigin(),
            'redirectUri' => Analytics::$plugin->oauth->getRedirectUri(),
            'googleIconUrl' => Craft::$app->assetManager->getPublishedUrl('@dukt/analytics/icons/google.svg', true),
        ]);
    }
}