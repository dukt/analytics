<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\StringHelper;
use craft\helpers\Json;
use dukt\analytics\web\assets\reportwidget\ReportWidgetAsset;
use dukt\analytics\Plugin as Analytics;

class ReportWidget extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    public $realtime;
    public $chart;
    public $period;
    public $options;

	// Public Methods
	// =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('analytics', 'Analytics Report');
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

		return Craft::t('analytics', 'Analytics Report');
	}

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@dukt/analytics/icons/report.svg');
    }

	/**
	 * @inheritDoc IWidget::getBodyHtml()
	 *
	 * @return string|false
	 */
	public function getBodyHtml()
	{
		if(Analytics::$plugin->analytics->checkPluginRequirements())
		{
			if(Craft::$app->config->get('enableWidgets', 'analytics'))
			{
				$settings = $this->settings;

				$profileId = Analytics::$plugin->analytics->getProfileId();

				if($profileId)
				{
					$request = [
						'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
						'period' => (isset($settings['period']) ? $settings['period'] : null),
						'options' => (isset($settings['options'][$settings['chart']]) ? $settings['options'][$settings['chart']] : null),
					];


					// use cached response if available

					if(Craft::$app->config->get('enableCache', 'analytics') === true)
					{
						$cacheId = ['getReport', $request, $profileId];

						$cachedResponse = Analytics::$plugin->cache->get($cacheId);
					}


					// render

					$widgetId = $this->id;

					$widgetOptions = [
						'request' => $request,
						'cachedResponse' => isset($cachedResponse) ? $cachedResponse : null,
					];

                    Craft::$app->getView()->registerAssetBundle(ReportWidgetAsset::class);

                    $jsTemplate = 'window.csrfTokenName = "{{ craft.app.config.get(\'csrfTokenName\')|e(\'js\') }}";';
					$jsTemplate .= 'window.csrfTokenValue = "{{ craft.app.request.csrfToken|e(\'js\') }}";';
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
					'metrics' => Analytics::$plugin->metadata->getSelectMetricOptions()
				];

				break;

			case 'counter':

				$options = [
					'metrics' => Analytics::$plugin->metadata->getSelectMetricOptions()
				];

				break;

			case 'geo':

				$options = [
					'dimensions' => Analytics::$plugin->metadata->getSelectDimensionOptions(['ga:city', 'ga:country', 'ga:continent', 'ga:subContinent']),
					'metrics' => Analytics::$plugin->metadata->getSelectMetricOptions()
				];

				break;

			default:

				$options = [
					'dimensions' => Analytics::$plugin->metadata->getSelectDimensionOptions(),
					'metrics' => Analytics::$plugin->metadata->getSelectMetricOptions()
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
					$name[] = Craft::t('app', Analytics::$plugin->metadata->getDimMet($options['dimension']));
				}

				if(!empty($options['metric']))
				{
					$name[] = Craft::t('app', Analytics::$plugin->metadata->getDimMet($options['metric']));
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
