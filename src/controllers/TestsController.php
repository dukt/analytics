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
     * Data Types
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionDataTypes(array $variables = array())
    {
        $variables['googleAnalyticsDataTypes'] = Analytics::$plugin->metadata->getGoogleAnalyticsDataTypes();
        $variables['dataTypes'] = Analytics::$plugin->metadata->getDataTypes();

        return $this->renderTemplate('analytics/tests/_dataTypes', $variables);
    }

    /**
     * Charts
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionReportWidgets(array $variables = array())
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

        return $this->renderTemplate('analytics/tests/_reportWidgets', $variables);
    }

    /**
     * @param array $variables
     *
     * @return Response
     */
    public function actionFormatting(array $variables = array())
    {
        return $this->renderTemplate('analytics/tests/_formatting', $variables);
    }

    /**
     * Columns
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionColumns(array $variables = array())
    {
        $variables['columns'] = Analytics::$plugin->metadata->getColumns();

        return $this->renderTemplate('analytics/tests/_columns', $variables);
    }

    /**
     * Groups
     *
     * @param array $variables
     *
     * @return Response
     */
    public function actionColumnGroups(array $variables = array())
    {
        $variables['columnGroups'] = Analytics::$plugin->metadata->getColumnGroups();

        return $this->renderTemplate('analytics/tests/_columnGroups', $variables);
    }
}