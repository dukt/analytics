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
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\analyticsvue\AnalyticsVueAsset;
use dukt\analytics\web\assets\tests\TestsAsset;
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
     * Columns
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionColumns(array $variables = [])
    {
        $variables['columns'] = Analytics::$plugin->getMetadata()->getColumns();

        return $this->renderTemplate('analytics/tests/_columns', $variables);
    }

    /**
     * Column Groups
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionColumnGroups(array $variables = [])
    {
        $variables['columnGroups'] = Analytics::$plugin->getMetadata()->getColumnGroups();

        return $this->renderTemplate('analytics/tests/_columnGroups', $variables);
    }

    /**
     * Data Types
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionDataTypes(array $variables = [])
    {
        $variables['googleAnalyticsDataTypes'] = Analytics::$plugin->getMetadata()->getGoogleAnalyticsDataTypes();
        $variables['dataTypes'] = Analytics::$plugin->getMetadata()->getDataTypes();

        return $this->renderTemplate('analytics/tests/_dataTypes', $variables);
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
        Craft::$app->getView()->registerJs('new AnalyticsVueReportWidget().$mount("#report-widget");');
        Craft::$app->getView()->registerJs('new AnalyticsVueReportWidgetSettings().$mount("#report-widget-settings");');

        return $this->renderTemplate('analytics/tests/_vue', $variables);
    }
}