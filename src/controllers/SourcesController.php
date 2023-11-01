<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use dukt\analytics\models\Source;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\Plugin as Analytics;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SourcesController extends Controller
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

        $variables = [
            'isConnected' => false
        ];

        try {
            $token = Analytics::$plugin->getOauth()->getToken();

            if ($isOauthProviderConfigured && $token) {
                $variables['isConnected'] = true;
                $variables['sources'] = Analytics::$plugin->getSources()->getSources();
            }
        } catch (IdentityProviderException $identityProviderException) {
            $variables['error'] = $identityProviderException->getMessage();

            $data = $identityProviderException->getResponseBody();

            if (isset($data['error_description'])) {
                $variables['error'] = $data['error_description'];
            }
        }

        return $this->renderTemplate('analytics/sources/_index', $variables);
    }

    /**
     * Edit a source.
     *
     * @param int|null  $sourceId
     * @param Source|null $source
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionEdit(int $sourceId = null, Source $source = null): Response
    {
        $variables = [];
        $variables['isNewSource'] = false;

        if ($sourceId !== null) {
            if (!$source instanceof \dukt\analytics\models\Source) {
                $source = Analytics::$plugin->getSources()->getSourceById($sourceId);

                if (!$source instanceof \dukt\analytics\models\Source) {
                    throw new NotFoundHttpException('View not found');
                }
            }

            $variables['title'] = $source->name;
            $variables['source'] = $source;
        } else {
            if ($source === null) {
                $source = new Source();
                $variables['isNewSource'] = true;
            }

            $variables['title'] = Craft::t('analytics', 'Create a new source');
        }

        $variables['source'] = $source;
        $variables['accountExplorerOptions'] = $this->getAccountExplorerOptions($source);

        Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

        $jsOptions = [
            'source' => $variables['source'],
            'accountExplorerOptions' => $variables['accountExplorerOptions'],
        ];

        Craft::$app->getView()->registerJs('new AnalyticsVueSettings({data: {pluginOptions: '.Json::encode($jsOptions).'}}).$mount("#analytics-settings");');

        return $this->renderTemplate('analytics/sources/_edit', $variables);
    }

    /**
     * Saves a source.
     *
     * @return null|Response
     * @throws \dukt\analytics\errors\InvalidViewException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $accountExplorer = $request->getBodyParam('accountExplorer');

        $source = new Source();
        $source->id = $request->getBodyParam('sourceId');
        $source->type = $accountExplorer['type'];
        $source->name = $request->getBodyParam('name');
        $source->gaAccountId = $accountExplorer['account'];
        $source->gaPropertyId = $accountExplorer['property'];
        $source->gaViewId = $accountExplorer['view'] ?? null;
        $accountExplorerData = Analytics::$plugin->getApis()->getAnalytics()->getAccountExplorerData();

        foreach ($accountExplorerData['accounts'] as $dataAccount) {
            if ($dataAccount->id == $source->gaAccountId) {
                $source->gaAccountName = $dataAccount->name;
            }
        }

        foreach ($accountExplorerData['properties'] as $dataProperty) {
            if ($dataProperty['id'] == $source->gaPropertyId) {
                $source->gaPropertyName = $dataProperty['name'];
                $source->gaCurrency = $dataProperty['currency'];
            }
        }

        foreach ($accountExplorerData['views'] as $dataView) {
            if ($dataView->id == $source->gaViewId) {
                $source->gaViewName = $dataView->name;
                $source->gaCurrency = $dataView->currency;
            }
        }

        // Save it
        if (!Analytics::$plugin->getSources()->saveSource($source)) {
            Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t save the source.'));

            // Send the view back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'source' => $source
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('analytics', 'View saved.'));

        return $this->redirectToPostedUrl($source);
    }

    /**
     * Deletes a source.
     *
     * @return Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $sourceId = $request->getRequiredBodyParam('id');

        Analytics::$plugin->getSources()->deleteSourceById($sourceId);

        return $this->asJson(['success' => true]);
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

    // Private Methods
    // =========================================================================

    /**
     * @param Source $source
     *
     * @return array
     */
    private function getAccountExplorerOptions(Source $source): array
    {
        $accountExplorerData = Analytics::$plugin->getCache()->get(['accountExplorerData']);

        return [
            'accounts' => $this->getAccountOptions($accountExplorerData, $source),
            'properties' => $this->getPropertyOptions($accountExplorerData, $source),
            'views' => $this->getViewOptions($accountExplorerData, $source),
        ];
    }

    /**
     * @param      $accountExplorerData
     * @param Source $source
     *
     * @return array
     */
    private function getAccountOptions($accountExplorerData, Source $source): array
    {
        $accountOptions = [];

        if (isset($accountExplorerData['accounts'])) {
            foreach ($accountExplorerData['accounts'] as $account) {
                $accountOptions[] = ['label' => $account->name, 'value' => $account->id];
            }
        } else {
            $accountOptions[] = ['label' => $source->gaAccountName, 'value' => $source->gaAccountId];
        }

        return $accountOptions;
    }

    /**
     * @param      $accountExplorerData
     * @param Source $source
     *
     * @return array
     */
    private function getPropertyOptions($accountExplorerData, Source $source): array
    {
        $propertyOptions = [];

        if (isset($accountExplorerData['properties'])) {
            foreach ($accountExplorerData['properties'] as $webProperty) {
                $propertyOptions[] = ['label' => $webProperty['name'], 'value' => $webProperty['id']];
            }
        } else {
            $propertyOptions[] = ['label' => $source->gaPropertyName, 'value' => $source->gaPropertyId];
        }

        return $propertyOptions;
    }

    /**
     * @param      $accountExplorerData
     * @param Source $source
     *
     * @return array
     */
    private function getViewOptions($accountExplorerData, Source $source): array
    {
        $viewOptions = [];

        if (isset($accountExplorerData['views'])) {
            foreach ($accountExplorerData['views'] as $dataView) {
                $viewOptions[] = ['label' => $dataView->name, 'value' => $dataView->id];
            }
        } else {
            $viewOptions[] = ['label' => $source->gaViewName, 'value' => $source->gaViewId];
        }

        return $viewOptions;
    }
}