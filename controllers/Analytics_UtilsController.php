<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_UtilsController extends BaseController
{
	// Properties
	// =========================================================================

	private $addedInApiVersion = 3;

	// Public Methods
	// =========================================================================

	public function actionMetadata(array $variables = array())
	{
		$variables['dimensions'] = craft()->analytics_metadata->getDimensions();
		$variables['metrics'] = craft()->analytics_metadata->getMetrics();

		$variables['dimmetsFileExists'] = craft()->analytics_metadata->dimmetsFileExists();

		$this->renderTemplate('analytics/utils/metadata/_index', $variables);
	}

	public function actionSearchMetadata()
	{
		$q = craft()->request->getParam('q');
		$columns = craft()->analytics_metadata->searchColumns($q);

		// Send the source back to the template
		craft()->urlManager->setRouteVariables(array(
			'q' => $q,
			'columns' => $columns,
		));
	}

	public function actionloadMetadata()
	{
		$this->_deleteMetadata();
		$this->_importMetadata();

		craft()->userSession->setNotice(Craft::t("Metadata loaded."));

		$referer = craft()->request->getUrlReferrer();
		$this->redirect($referer);
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

		$this->renderTemplate('analytics/utils/_dataTypes', $variables);
	}


	/**
	 * Tests
	 *
	 * @return null
	 */
	public function actionTests(array $variables = array())
	{
		craft()->templates->includeJsResource('analytics/js/Analytics.js');
		$this->renderTemplate('analytics/utils/_tests', $variables);
	}

	/**
	 * Columns
	 *
	 * @return null
	 */
	public function actionColumns(array $variables = array())
	{
		$variables['columns'] = craft()->analytics_metadata->getColumns();

		$this->renderTemplate('analytics/utils/_columns', $variables);
	}

	/**
	 * Groups
	 *
	 * @return null
	 */
	public function actionColumnGroups(array $variables = array())
	{
		$variables['columnGroups'] = craft()->analytics_metadata->getColumnGroups();

		$this->renderTemplate('analytics/utils/_columnGroups', $variables);
	}

	// Private Methods
	// =========================================================================

	private function _deleteMetadata()
	{
		$path = craft()->analytics_metadata->getDimmetsFilePath();

		IOHelper::deleteFile($path);
	}

	private function _importMetadata()
	{
		$columns = [];

		$metadataColumns = craft()->analytics_api->getMetadataColumns();

		if($metadataColumns)
		{
			$items = $metadataColumns->listMetadataColumns('ga');

			if($items)
			{
				foreach($items as $item)
				{
					if($item->attributes['status'] == 'DEPRECATED')
					{
						continue;
					}

					if($item->attributes['addedInApiVersion'] > $this->addedInApiVersion)
					{
						continue;
					}

					if(isset($item->attributes['minTemplateIndex']))
					{
						for($i = $item->attributes['minTemplateIndex']; $i <= $item->attributes['maxTemplateIndex']; $i++)
						{
							$column = [];
							$column['id'] = str_replace('XX', $i, $item->id);
							$column['uiName'] = str_replace('XX', $i, $item->attributes['uiName']);
							$column['description'] = str_replace('XX', $i, $item->attributes['description']);

							$columns[$column['id']] = $this->populateColumnAttributes($column, $item);
						}
					}
					else
					{
						$column = [];
						$column['id'] = $item->id;
						$column['uiName'] = $item->attributes['uiName'];
						$column['description'] = $item->attributes['description'];

						$columns[$column['id']] = $this->populateColumnAttributes($column, $item);
					}
				}
			}
		}

		$contents = json_encode($columns);

		$path = craft()->analytics_metadata->getDimmetsFilePath();

		$res = IOHelper::writeToFile($path, $contents);
	}

	private function populateColumnAttributes($column, $item)
	{
		$column['type'] = $item->attributes['type'];
		$column['dataType'] = $item->attributes['dataType'];
		$column['group'] = $item->attributes['group'];
		$column['status'] = $item->attributes['status'];

		if(isset($item->attributes['allowInSegments']))
		{
			$column['allowInSegments'] = $item->attributes['allowInSegments'];
		}

		if(isset($item->attributes['addedInApiVersion']))
		{
			$column['addedInApiVersion'] = $item->attributes['addedInApiVersion'];
		}

		return $column;
	}
}