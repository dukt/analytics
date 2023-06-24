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
use yii\web\Response;
use Google\Service\AnalyticsData\RunReportRequest;

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
     * Template Variables
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionTemplateVariables(array $variables = [])
    {
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
        $request = new RunReportRequest();
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
}