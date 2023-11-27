<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\db\Query;
use craft\helpers\Json;
use dukt\analytics\models\Source;
use dukt\analytics\records\Source as SourceRecord;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\Plugin as Analytics;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class SourcesController extends BaseAccessController
{
    protected bool $requirePluginAccess = true;

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
                $variables['sources'] = Analytics::$plugin->getSources()->getSources('*');
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
                    throw new NotFoundHttpException('Source not found');
                }
            }

            $variables['title'] = $source->name;
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
        Craft::$app->getView()->registerTranslations('analytics', [
            'Loading Google Analytics accounts…',
            'Analytics Accounts',
            'Properties & Apps',
            'Select GA4 property',
        ]);


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

        $sourceId = $request->getBodyParam('sourceId');
        $sourceRecord = SourceRecord::findOne($sourceId);

        $source = new Source();
        $source->id = $request->getBodyParam('sourceId');
        $source->name = $request->getBodyParam('name');
        $source->type = $accountExplorer['type'];

        if ($source->type === 'GA4') {
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
        } else {
            $source->gaAccountId = $sourceRecord->gaAccountId;
            $source->gaAccountName = $sourceRecord->gaAccountName;
            $source->gaPropertyId = $sourceRecord->gaPropertyId;
            $source->gaPropertyName = $sourceRecord->gaPropertyName;
            $source->gaViewId = $sourceRecord->gaViewId;
            $source->gaViewName = $sourceRecord->gaViewName;
            $source->gaCurrency = $sourceRecord->gaCurrency;
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

        // Update widgets and fields with new dimensions and metrics
        if ($sourceRecord && $sourceRecord->type === 'UA' && $source->type === 'GA4') {
            $this->migrateDimensionsAndMetricsToGA4($sourceId);
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

    /**
     * @param int $sourceId
     * @return void
     * @throws Exception
     */
    private function migrateDimensionsAndMetricsToGA4(int $sourceId): void
    {
        $widgetRows = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%widgets}}'])
            ->where(['type' => 'dukt\analytics\widgets\Report'])
            ->all();

        foreach ($widgetRows as $widgetRow) {
            $widgetSettings = Json::decodeIfJson($widgetRow['settings']);

            if (
                is_array($widgetSettings)
                && isset($widgetSettings['sourceId'])
                && (int) $widgetSettings['sourceId'] === $sourceId
                && isset($widgetSettings['options'][$widgetSettings['chart']])
            ) {
                $chartOptions = $widgetSettings['options'][$widgetSettings['chart']];
                $newChartOptions = [];

                if (isset($chartOptions['metric'])) {
                    $newChartOptions['metric'] = $this->uaToGa4($chartOptions['metric']);
                }

                if (isset($chartOptions['dimension'])) {
                    $newChartOptions['dimension'] = $this->uaToGa4($chartOptions['dimension']);
                }

                $widgetSettings['options'] = [
                    $widgetSettings['chart'] => $newChartOptions
                ];

                // Update widget settings
                Craft::$app->getDb()->createCommand()
                    ->update('{{%widgets}}', [
                        'settings' => Json::encode($widgetSettings),
                    ], ['id' => $widgetRow['id']])
                    ->execute();
            }
        }
    }

    /**
     * @param string $uaDimensionOrMetric
     * @return string
     */
    private function uaToGa4(string $uaDimensionOrMetric): string
    {
        $uaToGa4 = [
            // User

            // - Metrics
            'ga:users' => 'totalUsers',
            'ga:newUsers' => 'newUsers',
            'ga:1dayUsers' => 'active1DayUsers',
            'ga:7dayUsers' => 'active7DayUsers',
            'ga:28dayUsers' => 'active28DayUsers',
            'ga:sessionsPerUser' => 'sessionsPerUser',

            // Session

            // - Metrics
            'ga:sessions' => 'sessions',
            'ga:sessionDuration' => 'userEngagementDuration',
            'ga:hits' => 'eventCount',

            // Traffic Sources

            // - Dimensions
            'ga:fullReferrer' => 'pageReferrer',
            'ga:campaign' => 'sessionCampaignName',
            'ga:source' => 'sessionSource',
            'ga:medium' => 'sessionMedium',
            'ga:campaignCode' => 'sessionCampaignId',

            // Adwords

            // - Dimensions
            'ga:adFormat' => 'adFormat',
            'ga:adGroup' => 'sessionGoogleAdsAdGroupName',
            'ga:adDistributionNetwork' => 'sessionGoogleAdsAdNetworkType',
            'ga:adMatchedQuery' => 'sessionGoogleAdsQuery',
            'ga:adwordsCampaignID' => 'sessionCampaignId',
            'ga:adwordsAdGroupID' => 'sessionGoogleAdsAdGroupId',
            'ga:adwordsCreativeID' => 'sessionGoogleAdsCreativeId',

            // - Metrics
            'ga:adClicks' => 'advertiserAdClicks',
            'ga:impressions' => 'advertiserAdImpressions',
            'ga:adCost' => 'adveristerAdCost',

            // Platform or Devide

            // - Dimensions
            'ga:browser' => 'browser',
            'ga:operatingSystem' => 'operatingSystem',
            'ga:operatingSystemVersion' => 'operatingSystemVersion',
            'ga:mobileDeviceBranding' => 'mobileDeviceBranding',
            'ga:mobileDeviceModel' => 'mobileDeviceModel',
            'ga:deviceCategory' => 'deviceCategory',
            'ga:dataSource' => 'platform',

            // Geo Network

            // - Dimensions
            'ga:country' => 'country',
            'ga:region' => 'region',
            'ga:city' => 'city',
            'ga:cityId' => 'cityId',
            'ga:countryIsoCode' => 'countryId',

            // System

            // - Dimensions
            'ga:language' => 'language',
            'ga:screenResolution' => 'screenResolution',

            // Page Tracking

            // - Dimensions
            'ga:hostname' => 'hostName',
            'ga:pagePath' => 'pagePathPlusQueryString',
            'ga:pageTitle' => 'pageTitle',

            // - Metrics
            'ga:pageviews' => 'screenPageViews',
            'ga:timeOnPage' => 'userEngagementDuration',

            // App Tracking

            // - Dimensions
            'ga:appVersion' => 'appVersion',
            'ga:screenName' => 'unifiedScreenName',

            // - Metrics
            'ga:screenviews' => 'screenPageViews',

            // Event Tracking

            // - Dimensions
            'ga:eventLabel' => 'eventName',

            // - Metrics
            'ga:totalEvents' => 'eventCount',
            'ga:eventValue' => 'eventValue',

            // Ecommerce

            // - Dimensions
            'ga:productBrand' => 'itemBrand',
            'ga:productCategory' => 'itemCategory',
            'ga:productCategoryHierarchy' => 'itemCategory',
            'ga:productSku' => 'itemId',
            'ga:productListName' => 'itemListName',
            'ga:productName' => 'itemName',
            'ga:internalPromotionCreative' => 'itemPromotionCreativeName',
            'ga:internalPromotionId' => 'itemPromotionId',
            'ga:internalPromotionName' => 'itemPromotionName',
            'ga:orderCouponCode' => 'orderCoupon',
            'ga:transactionId' => 'transactionId',

            // - Metrics
            'ga:productAddsToCart' => 'addToCarts',
            'ga:cartToDetailRate' => 'cartToViewRate',
            'ga:productCheckouts' => 'checkouts',
            'ga:uniquePurchases' => 'ecommercePurchases',
            'ga:productListCTR' => 'itemListClickThroughRate',
            'ga:productListClicks' => 'itemListClicks',
            'ga:productListViews' => 'itemListViews',
            'ga:internalPromotionCTR' => 'itemPromotionClickThroughRate',
            'ga:internalPromotionClicks' => 'itemPromotionClicks',
            'ga:internalPromotionViews' => 'itemPromotionViews',
            'ga:itemQuantity' => 'itemPurchaseQuantity',
            'ga:itemRevenue' => 'itemRevenue',
            'ga:productDetailViews' => 'itemViews',

            // Time

            // - Dimensions
            'ga:date' => 'date',
            'ga:year' => 'year',
            'ga:month' => 'month',
            'ga:week' => 'week',
            'ga:day' => 'day',
            'ga:hour' => 'hour',
            'ga:minute' => 'minute',
            'ga:nthMonth' => 'nthMonth',
            'ga:nthWeek' => 'nthWeek',
            'ga:nthDay' => 'nthDay',
            'ga:nthMinute' => 'nthMinute',
            'ga:dayOfWeek' => 'dayOfWeek',
            'ga:dateHour' => 'dateHour',
            'ga:dateHourMinute' => 'dateHourMinute',
            'ga:nthHour' => 'nthHour',

            // Audience

            // - Dimensions
            'ga:userAgeBracket' => 'userAgeBracket',
            'ga:userGender' => 'userGender',
            'ga:interestInMarketCategory' => 'brandingInterest',

            // Lifetime Value and Cohorts

            // - Dimensions
            'ga:acquisitionCampaign' => 'firstUserCampaignName',
            'ga:acquisitionMedium' => 'firstUserMedium',
            'ga:acquisitionSource' => 'firstUserSource',
            'ga:cohort' => 'cohort',
            'ga:cohortNthDay' => 'cohortNthDay',
            'ga:cohortNthMonth' => 'cohortNthMonth',
            'ga:cohortNthWeek' => 'cohortNthWeek',

            // - Metrics
            'ga:cohortActiveUsers' => 'cohortActiveUsers',
            'ga:cohortTotalUsers' => 'cohortTotalUsers',

            // Channel Grouping

            // - Dimensions
            'ga:channelGrouping' => 'sessionDefaultChannelGrouping',
        ];

        if ($uaToGa4[$uaDimensionOrMetric]) {
            return $uaToGa4[$uaDimensionOrMetric];
        }

        return $uaDimensionOrMetric;
    }
}