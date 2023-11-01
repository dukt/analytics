<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\errors\InvalidPluginException;
use craft\web\Controller;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\Plugin as Analytics;
use Exception;
use Google_Service_Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use yii\web\Response;

class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): Response
    {
        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        if ($isOauthProviderConfigured) {
            $errors = [];

            try {
                $provider = Analytics::$plugin->getOauth()->getOauthProvider();
                $token = Analytics::$plugin->getOauth()->getToken();

                if ($token !== null) {
                    $oauthAccount = Analytics::$plugin->getCache()->get(['getAccount', $token]);

                    if (!$oauthAccount) {
                        $oauthAccount = $provider->getResourceOwner($token);
                        Analytics::$plugin->getCache()->set(['getAccount', $token], $oauthAccount);
                    }

                    if ($oauthAccount) {
                        Craft::info("Account:\r\n".print_r($oauthAccount, true), __METHOD__);

                        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
                        $settings = $plugin->getSettings();
                    }
                }
            } catch (Google_Service_Exception $googleServiceException) {
                Craft::info('Couldn’t get OAuth account: '.$googleServiceException->getMessage(), __METHOD__);

                foreach ($googleServiceException->getErrors() as $error) {
                    $errors[] = $error['message'];
                }
            } catch (IdentityProviderException $identityProviderException) {
                $error = $identityProviderException->getMessage();
                $data = $identityProviderException->getResponseBody();

                if (isset($data['error_description'])) {
                    $error = $data['error_description'];
                }

                $errors[] = $error;
            } catch (Exception $exception) {
                if (method_exists($exception, 'getResponse')) {
                    Craft::info('Couldn’t get OAuth account: '.$exception->getResponse(), __METHOD__);
                } else {
                    Craft::info('Couldn’t get OAuth account: '.$exception->getMessage(), __METHOD__);
                }

                $errors[] = $exception->getMessage();
            }
        }

        Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

        return $this->renderTemplate('analytics/settings/_index', [
            'isOauthProviderConfigured' => $isOauthProviderConfigured,
            'errors' => $errors ?? null,
            'oauthAccount' => $oauthAccount ?? null,
            'settings' => $settings ?? null,
            'info' => Analytics::getInstance()->getInfo(),
            'googleIconUrl' => Craft::$app->assetManager->getPublishedUrl('@dukt/analytics/icons/google.svg', true),
        ]);
    }

    /**
     * OAuth Settings.
     *
     * @return Response
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionOauth(): Response
    {
        return $this->renderTemplate('analytics/settings/_oauth', [
            'javascriptOrigin' => Analytics::$plugin->getOauth()->getJavascriptOrigin(),
            'redirectUri' => Analytics::$plugin->getOauth()->getRedirectUri(),
            'googleIconUrl' => Craft::$app->assetManager->getPublishedUrl('@dukt/analytics/icons/google.svg', true),
            'settings' => Analytics::$plugin->getSettings(),
        ]);
    }

    /**
     * Saves the settings.
     *
     * @return null|Response
     * @throws InvalidPluginException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        $pluginHandle = Craft::$app->getRequest()->getRequiredBodyParam('pluginHandle');
        $settings = Craft::$app->getRequest()->getBodyParam('settings');
        $plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);

        if (!$plugin) {
            throw new InvalidPluginException($pluginHandle);
        }

        $settings = Analytics::$plugin->getApis()->getAnalytics()->populateAccountExplorerSettings($settings);

        if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settings)) {
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
}