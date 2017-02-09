<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\StringHelper;
use craft\helpers\Json;
use dukt\analytics\web\assets\reportwidget\ReportWidgetAsset;

class ReportWidget extends \craft\base\Widget
{
    public $realtime;
    public $chart;
    public $period;
    public $options;

	// Public Methods
	// =========================================================================

	/**
	 * @inheritDoc IComponentType::getName()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('app', 'Analytics Report');
	}

	/**
	 * @inheritDoc IWidget::getTitle()
	 *
	 * @return string
	 */
	public function getTitle(): string
	{
		$reportTitle = $this->_getReportTitle();

		if($reportTitle)
		{
			return $reportTitle;
		}

		return Craft::t('app', 'Analytics Report');
	}

	/**
	 * @inheritDoc IWidget::getIconPath()
	 *
	 * @return string
	 */
	public function getIconPath()
	{
		return Craft::$app->resources->getResourcePath('analytics/images/widgets/stats.svg');
	}

	/**
	 * @inheritDoc IWidget::getBodyHtml()
	 *
	 * @return string|false
	 */
	public function getBodyHtml()
	{
		if(\dukt\analytics\Plugin::getInstance()->analytics->checkPluginRequirements())
		{
			if(Craft::$app->config->get('enableWidgets', 'analytics'))
			{
				$settings = $this->settings;

				$profileId = \dukt\analytics\Plugin::getInstance()->analytics->getProfileId();

				if($profileId)
				{
					/*Craft::$app->getView()->registerJsFile('analytics/js/jsapi.js');

					Craft::$app->getView()->registerJsFile('analytics/js/ReportWidgetSettings.js');
					Craft::$app->getView()->registerJsFile('analytics/js/ReportWidget.js');*/

/*					Craft::$app->getView()->registerCssFile('analytics/css/ReportWidget.css');
					Craft::$app->getView()->registerCssFile('analytics/css/ReportWidgetSettings.css');*/

                    Craft::$app->getView()->registerAssetBundle(ReportWidgetAsset::class);

					$request = [
						'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
						'period' => (isset($settings['period']) ? $settings['period'] : null),
						'options' => (isset($settings['options'][$settings['chart']]) ? $settings['options'][$settings['chart']] : null),
					];


					// use cached response if available

					if(Craft::$app->config->get('enableCache', 'analytics') === true)
					{
						$cacheId = ['getReport', $request, $profileId];

						$cachedResponse = \dukt\analytics\Plugin::getInstance()->analytics_cache->get($cacheId);
					}


					// render

					$widgetId = $this->id;

					$widgetOptions = [
						'request' => $request,
						'cachedResponse' => isset($cachedResponse) ? $cachedResponse : null,
					];

					$jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}";';
					$jsTemplate .= 'window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
					$js = Craft::$app->getView()->renderString($jsTemplate);
					Craft::$app->getView()->registerJs($js);
					Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::t('app', 'analyticsChartLanguage').'";');
					Craft::$app->getView()->registerJs('new Analytics.ReportWidget("widget'.$widgetId.'", '.Json::encode($widgetOptions).');');

					return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Report/body');
				}
				else
				{
					return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
				}
			}
			else
			{
				return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Report/disabled');
			}
		}
		else
		{
			return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
		}
	}

	/**
	 * @inheritDoc ISavableComponentType::getSettingsHtml()
	 *
	 * @return string
	 */
	public function getSettingsHtml()
	{
        Craft::$app->getView()->registerAssetBundle(ReportWidgetAsset::class);

		/*Craft::$app->getView()->registerJsFile('analytics/js/ReportWidgetSettings.js');
		Craft::$app->getView()->registerCssFile('analytics/css/ReportWidgetSettings.css');*/

		$id = 'analytics-settings-'.StringHelper::randomString();
		$namespaceId = Craft::$app->getView()->namespaceInputId($id);

		Craft::$app->getView()->registerJs("new Analytics.ReportWidgetSettings('".$namespaceId."');");

		$settings = $this->getSettings();

		// select options

		$chartTypes = ['area', 'counter', 'pie', 'table', 'geo'];

		$selectOptions = [];

		foreach($chartTypes as $chartType)
		{
			$selectOptions[$chartType] = $this->_geSelectOptionsByChartType($chartType);
		}

		return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Report/settings', array(
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
					'metrics' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			case 'counter':

				$options = [
					'metrics' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			case 'geo':

				$options = [
					'dimensions' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectDimensionOptions(['ga:city', 'ga:country', 'ga:continent', 'ga:subContinent']),
					'metrics' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectMetricOptions()
				];

				break;

			default:

				$options = [
					'dimensions' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectDimensionOptions(),
					'metrics' => \dukt\analytics\Plugin::getInstance()->analytics_metadata->getSelectMetricOptions()
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
					$name[] = Craft::t('app', \dukt\analytics\Plugin::getInstance()->analytics_metadata->getDimMet($options['dimension']));
				}

				if(!empty($options['metric']))
				{
					$name[] = Craft::t('app', \dukt\analytics\Plugin::getInstance()->analytics_metadata->getDimMet($options['metric']));
				}
			}

			if(!empty($this->settings['period']))
			{
				$name[] = Craft::t('app', ucfirst($this->settings['period']));
			}

			if(count($name) > 0)
			{
				return implode(" - ", $name);
			}
		}
		catch(\Exception $e)
		{
			// \dukt\analytics\Plugin::log('Couldn’t get Analytics Report’s title: '.$e->getMessage(), LogLevel::Error);
		}
	}
}
