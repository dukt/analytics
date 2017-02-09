<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;

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
		$variables['googleAnalyticsDataTypes'] = \dukt\analytics\Plugin::getInstance()->analytics_metadata->getGoogleAnalyticsDataTypes();
		$variables['dataTypes'] = \dukt\analytics\Plugin::getInstance()->analytics_metadata->getDataTypes();

		$this->renderTemplate('analytics/tests/_dataTypes', $variables);
	}

	/**
	 * Charts
	 *
	 * @return null
	 */
	public function actionReportWidgets(array $variables = array())
	{
		Craft::$app->getView()->registerJsFile('analytics/js/jsapi.js', true);

		Craft::$app->getView()->registerJsFile('analytics/js/ReportWidget.js');
		Craft::$app->getView()->registerCssFile('analytics/css/ReportWidget.css');
		Craft::$app->getView()->registerCssFile('analytics/css/tests.css');

		$this->renderTemplate('analytics/tests/_reportWidgets', $variables);
	}

	/**
	 * Tests
	 *
	 * @return null
	 */
	public function actionFormatting(array $variables = array())
	{
		$variables['currency'] = \dukt\analytics\Plugin::getInstance()->analytics->getCurrency();

		$this->renderTemplate('analytics/tests/_formatting', $variables);
	}

	/**
	 * Columns
	 *
	 * @return null
	 */
	public function actionColumns(array $variables = array())
	{
		$variables['columns'] = \dukt\analytics\Plugin::getInstance()->analytics_metadata->getColumns();

		$this->renderTemplate('analytics/tests/_columns', $variables);
	}

	/**
	 * Groups
	 *
	 * @return null
	 */
	public function actionColumnGroups(array $variables = array())
	{
		$variables['columnGroups'] = \dukt\analytics\Plugin::getInstance()->analytics_metadata->getColumnGroups();

		$this->renderTemplate('analytics/tests/_columnGroups', $variables);
	}
}