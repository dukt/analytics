<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;

class AnalyticsController extends Controller
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
		$widgetId = Craft::$app->request->getPost('id');

		$formerWidget = Craft::$app->dashboard->getUserWidgetById($widgetId);

		if($formerWidget)
		{
			$postSettings = Craft::$app->request->getPost('settings');

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

			if (Craft::$app->dashboard->saveUserWidget($widget))
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
