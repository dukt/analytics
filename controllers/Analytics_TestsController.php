<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_TestsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Account Explorer
	 *
	 * @return null
	 */
	public function actionAccountExplorer(array $variables = array())
	{
		$variables['accounts'] = craft()->analytics_api->getAccounts();
		$variables['webProperties'] = craft()->analytics_api->getWebProperties();
		$variables['profiles'] = craft()->analytics_api->getProfiles();


		$this->renderTemplate('analytics/tests/_accountExplorer', $variables);
	}

    /**
     * Get Account Explorer Data
     *
     * @return null
     */
    public function actionGetAccountExplorerData()
    {
        // Accounts
        $apiAccounts = craft()->analytics_api->getAccounts();
        $accounts = $apiAccounts->toSimpleObject()->items;

        // Properties
        $apiProperties = craft()->analytics_api->getProperties();
        $properties = $apiProperties->toSimpleObject()->items;

        // Views
        $apiViews = craft()->analytics_api->getProfiles();
        $views = $apiViews->toSimpleObject()->items;

        // Return JSON
        $this->returnJson(array(
            'accounts' => $accounts,
            'properties' => $properties,
            'views' => $views,
        ));
    }


	/**
	 * Data Types
	 *
	 * @return null
	 */
	public function actionDataTypes(array $variables = array())
	{
		$variables['googleAnalyticsDataTypes'] = craft()->analytics_metadata->getGoogleAnalyticsDataTypes();
		$variables['dataTypes'] = craft()->analytics_metadata->getDataTypes();

		$this->renderTemplate('analytics/tests/_dataTypes', $variables);
	}

	/**
	 * Charts
	 *
	 * @return null
	 */
	public function actionReportWidgets(array $variables = array())
	{
		craft()->templates->includeJsResource('analytics/js/jsapi.js', true);

		craft()->templates->includeJsResource('analytics/js/ReportWidget.js');
		craft()->templates->includeCssResource('analytics/css/ReportWidget.css');
		craft()->templates->includeCssResource('analytics/css/tests.css');

		$this->renderTemplate('analytics/tests/_reportWidgets', $variables);
	}

	/**
	 * Tests
	 *
	 * @return null
	 */
	public function actionFormatting(array $variables = array())
	{
		$variables['currency'] = craft()->analytics->getCurrency();

		$this->renderTemplate('analytics/tests/_formatting', $variables);
	}

	/**
	 * Columns
	 *
	 * @return null
	 */
	public function actionColumns(array $variables = array())
	{
		$variables['columns'] = craft()->analytics_metadata->getColumns();

		$this->renderTemplate('analytics/tests/_columns', $variables);
	}

	/**
	 * Groups
	 *
	 * @return null
	 */
	public function actionColumnGroups(array $variables = array())
	{
		$variables['columnGroups'] = craft()->analytics_metadata->getColumnGroups();

		$this->renderTemplate('analytics/tests/_columnGroups', $variables);
	}
}