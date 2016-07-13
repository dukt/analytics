<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Save Widget State
	 *
	 * @return null
	 */
	public function actionSaveWidgetState()
	{
		$widgetId = craft()->request->getPost('id');

		$formerWidget = craft()->dashboard->getUserWidgetById($widgetId);

		if($formerWidget)
		{
			$postSettings = craft()->request->getPost('settings');

			$widgetSettings = [
				'chart' => $postSettings['chart'],
				'period' => $postSettings['period'],
			];

			if(isset($postSettings['options']))
			{
				$widgetSettings['options'] = $postSettings['options'];
			}

			$widget = new WidgetModel();
			$widget->id = $widgetId;
			$widget->type = $formerWidget->type;
			$widget->settings = $widgetSettings;

			if (craft()->dashboard->saveUserWidget($widget))
			{
				$this->returnJson(true);
			}
			else
			{
				$this->returnErrorJson('Couldn’t save widget');
			}
		}
		else
		{
			$this->returnErrorJson('Couldn’t save widget');
		}
	}
}
