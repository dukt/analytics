<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\tests\TestsAsset;

class TestsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Data Types
     *
     * @return null
     */
    public function actionDataTypes(array $variables = array())
    {
        $variables['googleAnalyticsDataTypes'] = Analytics::$plugin->metadata->getGoogleAnalyticsDataTypes();
        $variables['dataTypes'] = Analytics::$plugin->metadata->getDataTypes();

        $this->renderTemplate('analytics/tests/_dataTypes', $variables);
    }

    /**
     * Charts
     *
     * @return null
     */
    public function actionReportWidgets(array $variables = array())
    {
/*        Craft::$app->getView()->registerJsFile('analytics/js/jsapi.js', true);

        Craft::$app->getView()->registerJsFile('analytics/js/ReportWidget.js');
        Craft::$app->getView()->registerCssFile('analytics/css/ReportWidget.css');
        Craft::$app->getView()->registerCssFile('analytics/css/tests.css');*/
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

        $this->renderTemplate('analytics/tests/_reportWidgets', $variables);
    }

    /**
     * Tests
     *
     * @return null
     */
    public function actionFormatting(array $variables = array())
    {
        $this->renderTemplate('analytics/tests/_formatting', $variables);
    }

    /**
     * Columns
     *
     * @return null
     */
    public function actionColumns(array $variables = array())
    {
        $variables['columns'] = Analytics::$plugin->metadata->getColumns();

        $this->renderTemplate('analytics/tests/_columns', $variables);
    }

    /**
     * Groups
     *
     * @return null
     */
    public function actionColumnGroups(array $variables = array())
    {
        $variables['columnGroups'] = Analytics::$plugin->metadata->getColumnGroups();

        $this->renderTemplate('analytics/tests/_columnGroups', $variables);
    }
}