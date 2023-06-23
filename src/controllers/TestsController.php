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
use craft\web\View;
use dukt\analytics\Plugin;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\analyticsvue\AnalyticsVueAsset;
use dukt\analytics\web\assets\tests\TestsAsset;
use Google\Service\AnalyticsData;
use yii\web\Response;

/**
 * Class TestsController
 *
 * @package dukt\analytics\controllers
 */
class TestsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Overview
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionOverview(array $variables = [])
    {
        return $this->renderTemplate('analytics/tests/_overview', $variables);
    }

    /**
     * Formatting
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionFormatting(array $variables = [])
    {
        $currencyDefinition = Analytics::$plugin->getAnalytics()->getCurrencyDefinition();

        $js = 'AnalyticsCurrencyDefinition = '.Json::encode($currencyDefinition).';';

        Craft::$app->getView()->registerJs($js, View::POS_BEGIN);

        return $this->renderTemplate('analytics/tests/_formatting', $variables);
    }

    /**
     * Report Widgets
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionReportWidgets(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

        return $this->renderTemplate('analytics/tests/_reportWidgets', $variables);
    }

    /**
     * Template Variables
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionTemplateVariables(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

        return $this->renderTemplate('analytics/tests/_templateVariables', $variables);
    }

    /**
     * GA4
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionGa4(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

//        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
//        $request->setViewId('42395806');
//        $request->setDateRanges([
//            'startDate' => '7daysAgo',
//            'endDate' => 'today',
//        ]);
//        $request->setMetrics([
//            'expression' => 'ga:users',
//        ]);
//        $request->setDimensions([
//            'name' => 'ga:browser',
//        ]);
//
//
//        $reportsRequest = new \Google_Service_AnalyticsReporting_GetReportsRequest();
//        $reportsRequest->setReportRequests([$request]);
//
//        $response = Analytics::$plugin->getApis()->getAnalyticsReporting()->getService()->reports->batchGet($reportsRequest);
//

        $request = new \Google\Service\AnalyticsData\RunReportRequest();
        $request->setDateRanges([
            'startDate' => '7daysAgo',
            'endDate' => 'today',
        ]);
        $request->setMetrics([
            'name' => 'newUsers',
        ]);
        $request->setDimensions([
            'name' => 'browser',
        ]);

        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
        $variables['reportResponse'] = $analyticsData->properties->runReport('properties/309469168', $request);

        return $this->renderTemplate('analytics/tests/_ga4', $variables);
    }

    public function actionGa4Metadata(array $variables = [])
    {
        $analyticsData = Plugin::$plugin->getApis()->getAnalytics()->getAnalyticsData();
        $metadata = $analyticsData->properties->getMetadata('properties/309469168/metadata');
        $variables['metadata'] = $metadata;

        $dimCategories = [];

        foreach($metadata->getDimensions() as $dim) {
            $dimCategories[$dim->getCategory()] = $dim->getCategory();
        }

        $variables['dimCategories'] = $dimCategories;

        $metCategories = [];

        foreach($metadata->getDimensions() as $met) {
            $metCategories[$met->getCategory()] = $met->getCategory();
        }

        $variables['metCategories'] = $metCategories;

        $variables['dimensions'] = $metadata->getDimensions();
        $variables['metrics'] = $metadata->getMetrics();

        return $this->renderTemplate('analytics/tests/_ga4-metadata', $variables);
    }

    /**
     * Vue
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionVue(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);
        Craft::$app->getView()->registerAssetBundle(AnalyticsVueAsset::class);
        Craft::$app->getView()->registerJs('new AnalyticsVueTests().$mount("#analytics-vue-tests");');
        Craft::$app->getView()->registerJs('new AnalyticsVueTestReportWidget().$mount("#report-widget");');
        Craft::$app->getView()->registerJs('new AnalyticsVueTestReportWidgetSettings().$mount("#report-widget-settings");');

        return $this->renderTemplate('analytics/tests/_vue', $variables);
    }

    /**
     * Vue
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionVueReports(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);
        Craft::$app->getView()->registerAssetBundle(AnalyticsVueAsset::class);
        Craft::$app->getView()->registerJs('new AnalyticsVueTestReports().$mount("#analytics-vue-test-reports");');

        return $this->renderTemplate('analytics/tests/_vue-reports', $variables);
    }
}