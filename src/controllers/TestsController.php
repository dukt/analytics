<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2020, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
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
    public function actionDataTypes(array $variables = [])
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
     * @throws \yii\base\InvalidConfigException
     */
    public function actionReportWidgets(array $variables = [])
    {
        Craft::$app->getView()->registerAssetBundle(TestsAsset::class);

        return $this->renderTemplate('analytics/tests/_reportWidgets', $variables);
    }

    /**
     * @param array $variables
     *
     * @return Response
     */
    public function actionFormatting(array $variables = [])
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
    public function actionColumns(array $variables = [])
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
    public function actionColumnGroups(array $variables = [])
    {
        $variables['columnGroups'] = Analytics::$plugin->metadata->getColumnGroups();

        return $this->renderTemplate('analytics/tests/_columnGroups', $variables);
    }
}