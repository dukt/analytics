<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\Json;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\realtimereportwidget\RealtimeReportWidgetAsset;

class RealtimeWidget extends \craft\base\Widget
{
	/**
	 * Whether users should be able to select more than one of this widget type.
	 *
	 * @var bool
	 */
	protected $multi = false;

	// Public Methods
	// =========================================================================

	public static function isSelectable(): bool
	{
		$plugin = Craft::$app->plugins->getPlugin('analytics');
		$settings = $plugin->getSettings();

		if(empty($settings['enableRealtime']))
		{
			return false;
		}

		return parent::isSelectable();
	}

	/**
	 * @inheritDoc IWidget::getTitle()
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('app', 'Analytics Real-Time');
	}

	/**
	 * @inheritDoc IWidget::getIconPath()
	 *
	 * @return string
	 */
	public function getIconPath()
	{
		return Craft::$app->resources->getResourcePath('analytics/images/widgets/realtime.svg');
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
				$profileId = Analytics::$plugin->analytics->getProfileId();

				if($profileId)
				{
					$plugin = Craft::$app->plugins->getPlugin('analytics');
					$settings = $plugin->getSettings();

					if(!empty($settings['enableRealtime']))
					{
						$realtimeRefreshInterval = Analytics::$plugin->analytics->getRealtimeRefreshInterval();

						$widgetId = $this->id;
                        $widgetOptions = [
                            'refreshInterval' => $realtimeRefreshInterval,
                        ];

                        Craft::$app->getView()->registerAssetBundle(RealtimeReportWidgetAsset::class);

						Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::$app->language.'";', true);

						Craft::$app->getView()->registerJs('new Analytics.Realtime("widget'.$widgetId.'", '.Json::encode($widgetOptions).');');

						return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/body');
					}
					else
					{
						return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/disabled');
					}
				}
				else
				{
					return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
				}
			}
			else
			{
				return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/disabled');
			}
		}
		else
		{
			return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
		}
	}

	/**
	 * @inheritDoc IWidget::getColspan()
	 *
	 * @return int
	 */
	public function getColSpan()
	{
		return 1;
	}
}
