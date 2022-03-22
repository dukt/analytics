<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\errors\InvalidPluginException;
use craft\web\Controller;
use dukt\analytics\models\SiteView;
use dukt\analytics\models\View;
use dukt\analytics\web\assets\settings\SettingsAsset;
use dukt\analytics\Plugin as Analytics;
use Exception;
use Google_Service_Exception;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use yii\web\NotFoundHttpException;
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

        Craft::$app->getView()->registerAssetBundle(SettingsAsset::class);

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

    /**
     * Returns the account explorer data.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetAccountExplorerData(): Response
    {
        $accountExplorerData = Analytics::$plugin->getApis()->getAnalytics()->getAccountExplorerData();

        Analytics::$plugin->getCache()->set(['accountExplorerData'], $accountExplorerData);

        return $this->asJson($accountExplorerData);
    }

    /**
     * Views index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionViews(): Response
    {
        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        $variables = [
            'isConnected' => false
        ];

        try {
            $token = Analytics::$plugin->getOauth()->getToken();

            if ($isOauthProviderConfigured && $token) {
                $variables['isConnected'] = true;
                $variables['reportingViews'] = Analytics::$plugin->getViews()->getViews();
            }
        } catch (IdentityProviderException $identityProviderException) {
            $variables['error'] = $identityProviderException->getMessage();

            $data = $identityProviderException->getResponseBody();

            if (isset($data['error_description'])) {
                $variables['error'] = $data['error_description'];
            }
        }

        return $this->renderTemplate('analytics/settings/views/_index', $variables);
    }

    /**
     * Edit a view.
     *
     * @param int|null  $viewId
     * @param View|null $reportingView
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEditView(int $viewId = null, View $reportingView = null): Response
    {
        $variables = [];
        $variables['isNewView'] = false;

        if ($viewId !== null) {
            if (!$reportingView instanceof \dukt\analytics\models\View) {
                $reportingView = Analytics::$plugin->getViews()->getViewById($viewId);

                if (!$reportingView instanceof \dukt\analytics\models\View) {
                    throw new NotFoundHttpException('View not found');
                }
            }

            $variables['title'] = $reportingView->name;
            $variables['reportingView'] = $reportingView;
        } else {
            if ($reportingView === null) {
                $reportingView = new View();
                $variables['isNewView'] = true;
            }

            $variables['title'] = Craft::t('analytics', 'Create a new view');
        }

        $variables['reportingView'] = $reportingView;
        $variables['accountExplorerOptions'] = $this->getAccountExplorerOptions($reportingView);

        Craft::$app->getView()->registerAssetBundle(SettingsAsset::class);

        return $this->renderTemplate('analytics/settings/views/_edit', $variables);
    }

    /**
     * Saves a view.
     *
     * @return null|Response
     * @throws \dukt\analytics\errors\InvalidViewException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveView()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $accountExplorer = $request->getBodyParam('accountExplorer');

        $reportingView = new View();
        $reportingView->id = $request->getBodyParam('viewId');
        $reportingView->name = $request->getBodyParam('name');
        $reportingView->gaAccountId = $accountExplorer['account'];
        $reportingView->gaPropertyId = $accountExplorer['property'];
        $reportingView->gaViewId = $accountExplorer['view'];

        $accountExplorerData = Analytics::$plugin->getApis()->getAnalytics()->getAccountExplorerData();

        foreach ($accountExplorerData['accounts'] as $dataAccount) {
            if ($dataAccount->id == $reportingView->gaAccountId) {
                $reportingView->gaAccountName = $dataAccount->name;
            }
        }

        foreach ($accountExplorerData['properties'] as $dataProperty) {
            if ($dataProperty->id == $reportingView->gaPropertyId) {
                $reportingView->gaPropertyName = $dataProperty->name;
            }
        }

        foreach ($accountExplorerData['views'] as $dataView) {
            if ($dataView->id == $reportingView->gaViewId) {
                $reportingView->gaViewName = $dataView->name;
                $reportingView->gaViewCurrency = $dataView->currency;
            }
        }

        // Save it
        if (!Analytics::$plugin->getViews()->saveView($reportingView)) {
            Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save the view.'));

            // Send the view back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'reportingView' => $reportingView
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('analytics', 'View saved.'));

        return $this->redirectToPostedUrl($reportingView);
    }

    /**
     * Deletes a view.
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteView(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $viewId = $request->getRequiredBodyParam('id');

        Analytics::$plugin->getViews()->deleteViewById($viewId);

        return $this->asJson(['success' => true]);
    }

    /**
     * Sites index.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSites(): Response
    {
        $isOauthProviderConfigured = Analytics::$plugin->getAnalytics()->isOauthProviderConfigured();

        $variables = [
            'isConnected' => false
        ];

        try {
            $token = Analytics::$plugin->getOauth()->getToken();

            if ($isOauthProviderConfigured && $token) {
                $variables['isConnected'] = true;
                $variables['sites'] = Craft::$app->getSites()->getAllSites();
                $variables['siteViews'] = Analytics::$plugin->getViews()->getSiteViews();
            }
        } catch (IdentityProviderException $identityProviderException) {
            $variables['error'] = $identityProviderException->getMessage();

            $data = $identityProviderException->getResponseBody();

            if (isset($data['error_description'])) {
                $variables['error'] = $data['error_description'];
            }
        }

        return $this->renderTemplate('analytics/settings/sites/_index', $variables);
    }

    /**
     * Edit a site.
     *
     * @param $siteId
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEditSite($siteId): Response
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);
        $siteView = Analytics::$plugin->getViews()->getSiteViewBySiteId($siteId);
        $reportingViews = Analytics::$plugin->getViews()->getViews();

        return $this->renderTemplate('analytics/settings/sites/_edit', [
            'site' => $site,
            'siteView' => $siteView,
            'reportingViews' => $reportingViews,
        ]);
    }

    /**
     * Saves a site.
     *
     * @return null|Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSite()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $siteView = new SiteView();
        $siteView->siteId = $request->getBodyParam('siteId');
        $siteView->viewId = $request->getBodyParam('viewId');

        // Save it
        if (!Analytics::$plugin->getViews()->saveSiteView($siteView)) {
            Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save the site view.'));

            // Send the view back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'siteView' => $siteView
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Site view saved.'));

        return $this->redirectToPostedUrl($siteView);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param View $reportingView
     *
     * @return array
     */
    private function getAccountExplorerOptions(View $reportingView): array
    {
        $accountExplorerData = Analytics::$plugin->getCache()->get(['accountExplorerData']);

        return [
            'accounts' => $this->getAccountOptions($accountExplorerData, $reportingView),
            'properties' => $this->getPropertyOptions($accountExplorerData, $reportingView),
            'views' => $this->getViewOptions($accountExplorerData, $reportingView),
        ];
    }

    /**
     * @param      $accountExplorerData
     * @param View $reportingView
     *
     * @return array
     */
    private function getAccountOptions($accountExplorerData, View $reportingView): array
    {
        $accountOptions = [];

        if (isset($accountExplorerData['accounts'])) {
            foreach ($accountExplorerData['accounts'] as $account) {
                $accountOptions[] = ['label' => $account->name, 'value' => $account->id];
            }
        } else {
            $accountOptions[] = ['label' => $reportingView->gaAccountName, 'value' => $reportingView->gaAccountId];
        }

        return $accountOptions;
    }

    /**
     * @param      $accountExplorerData
     * @param View $reportingView
     *
     * @return array
     */
    private function getPropertyOptions($accountExplorerData, View $reportingView): array
    {
        $propertyOptions = [];

        if (isset($accountExplorerData['properties'])) {
            foreach ($accountExplorerData['properties'] as $webProperty) {
                $propertyOptions[] = ['label' => $webProperty->name, 'value' => $webProperty->id];
            }
        } else {
            $propertyOptions[] = ['label' => $reportingView->gaPropertyName, 'value' => $reportingView->gaPropertyId];
        }

        return $propertyOptions;
    }

    /**
     * @param      $accountExplorerData
     * @param View $reportingView
     *
     * @return array
     */
    private function getViewOptions($accountExplorerData, View $reportingView): array
    {
        $viewOptions = [];

        if (isset($accountExplorerData['views'])) {
            foreach ($accountExplorerData['views'] as $dataView) {
                $viewOptions[] = ['label' => $dataView->name, 'value' => $dataView->id];
            }
        } else {
            $viewOptions[] = ['label' => $reportingView->gaViewName, 'value' => $reportingView->gaViewId];
        }

        return $viewOptions;
    }
}