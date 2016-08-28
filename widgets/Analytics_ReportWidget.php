<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportWidget extends BaseWidget
{
	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IComponentType::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Analytics Report');
	}

	/**
	 * @inheritDoc IWidget::getTitle()
	 *
	 * @return string
	 */
	public function getTitle()
	{
		$reportTitle = $this->_getReportTitle();

		if($reportTitle)
		{
			return $reportTitle;
		}

		return Craft::t('Analytics Report');
	}

	/**
	 * @inheritDoc IWidget::getIconPath()
	 *
	 * @return string
	 */
	public function getIconPath()
	{
		return craft()->resources->getResourcePath('analytics/images/widgets/stats.svg');
	}

	/**
	 * @inheritDoc IWidget::getBodyHtml()
	 *
	 * @return string|false
	 */
	public function getBodyHtml()
	{
		if(craft()->analytics->checkPluginRequirements())
		{
			if(craft()->config->get('enableWidgets', 'analytics'))
			{
				$settings = $this->settings;

				$profileId = craft()->analytics->getProfileId();

				if($profileId)
				{
					craft()->templates->includeJsResource('analytics/js/jsapi.js', true);

					craft()->templates->includeJsResource('analytics/js/ReportWidgetSettings.js');
					craft()->templates->includeJsResource('analytics/js/ReportWidget.js');

					craft()->templates->includeCssResource('analytics/css/ReportWidget.css');
					craft()->templates->includeCssResource('analytics/css/ReportWidgetSettings.css');

					$request = [
						'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
						'period' => (isset($settings['period']) ? $settings['period'] : null),
						'options' => (isset($settings['options'][$settings['chart']]) ? $settings['options'][$settings['chart']] : null),
					];


					// use cached response if available

					if(craft()->config->get('enableCache', 'analytics') === true)
					{
						$cacheId = ['getReport', $request, $profileId];

						$cachedResponse = craft()->analytics_cache->get($cacheId);
					}


					// render

					$widgetId = $this->model->id;

					$widgetOptions = [
						'request' => $request,
						'cachedResponse' => isset($cachedResponse) ? $cachedResponse : null,
					];

					$jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}";';
					$jsTemplate .= 'window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
					$js = craft()->templates->renderString($jsTemplate);
					craft()->templates->includeJs($js);
					craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
					craft()->templates->includeJs('new Analytics.ReportWidget("widget'.$widgetId.'", '.JsonHelper::encode($widgetOptions).');');

					return craft()->templates->render('analytics/_components/widgets/Report/body');
				}
				else
				{
					return craft()->templates->render('analytics/_special/plugin-not-configured');
				}
			}
			else
			{
				return craft()->templates->render('analytics/_components/widgets/Report/disabled');
			}
		}
		else
		{
			return craft()->templates->render('analytics/_special/plugin-not-configured');
		}
	}

	/**
	 * @inheritDoc ISavableComponentType::getSettingsHtml()
	 *
	 * @return string
	 */
	public function getSettingsHtml()
	{
		craft()->templates->includeJsResource('analytics/js/ReportWidgetSettings.js');
		craft()->templates->includeCssResource('analytics/css/ReportWidgetSettings.css');

		$id = 'analytics-settings-'.StringHelper::randomString();
		$namespaceId = craft()->templates->namespaceInputId($id);

		craft()->templates->includeJs("new Analytics.ReportWidgetSettings('".$namespaceId."');");

		$settings = $this->getSettings();

		// select options

		$chartTypes = ['area', 'counter', 'pie', 'table', 'geo'];

		$selectOptions = [];

		foreach($chartTypes as $chartType)
		{
			$selectOptions[$chartType] = $this->_geSelectOptionsByChartType($chartType);
		}

		return craft()->templates->render('analytics/_components/widgets/Report/settings', array(
		   'id' => $id,
		   'settings' => $settings,
		   'selectOptions' => $selectOptions,
		));
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseSavableComponentType::defineSettings()
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'realtime' => array(AttributeType::Bool),
			'chart' => array(AttributeType::String),
			'period' => array(AttributeType::String),
			'options' => array(AttributeType::Mixed),
		);
	}
	
	// Private Methods
	// =========================================================================

	/**
	 * Returns the dimension & metrics options for a given chart type
	 *
	 * @param $chartType
	 *
	 * @return array
	 */
	private function _geSelectOptionsByChartType($chartType)
	{
		switch($chartType)
		{
			case 'area':

				$options = [
					'metrics' => craft()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			case 'counter':

				$options = [
					'metrics' => craft()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			case 'geo':

				$options = [
					'dimensions' => craft()->analytics_metadata->getSelectDimensionOptions(['ga:city', 'ga:country', 'ga:continent', 'ga:subContinent']),
					'metrics' => craft()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			default:

				$options = [
					'dimensions' => craft()->analytics_metadata->getSelectDimensionOptions(),
					'metrics' => craft()->analytics_metadata->getSelectMetricOptions()
				];
		}

		return $options;
	}

	/**
	 * Returns the title of the report
	 *
	 * @return string|null
	 */
	private function _getReportTitle()
	{
		try
		{
			$name = [];
			$chartType = $this->settings['chart'];

			if(isset($this->settings['options'][$chartType]))
			{
				$options = $this->settings['options'][$chartType];

				if(!empty($options['dimension']))
				{
					$name[] = Craft::t(craft()->analytics_metadata->getDimMet($options['dimension']));
				}

				if(!empty($options['metric']))
				{
					$name[] = Craft::t(craft()->analytics_metadata->getDimMet($options['metric']));
				}
			}

			if(!empty($this->settings['period']))
			{
				$name[] = Craft::t(ucfirst($this->settings['period']));
			}

			if(count($name) > 0)
			{
				return implode(" - ", $name);
			}
		}
		catch(\Exception $e)
		{
			AnalyticsPlugin::log('Couldn’t get Analytics Report’s title: '.$e->getMessage(), LogLevel::Error);
		}
	}
}
