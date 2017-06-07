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

class Realtime extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    public $viewId;

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
        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
        $settings = $plugin->getSettings();

        if(empty($settings['enableRealtime']))
        {
            return false;
        }

        return parent::isSelectable();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('analytics', 'Analytics Real-time');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@dukt/analytics/icons/realtime-report.svg');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        if(Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
            if (Analytics::$plugin->getSettings()->enableWidgets) {
                $widgetSettings = $this->settings;

                if ($widgetSettings['viewId']) {
                    $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
                    $pluginSettings = $plugin->getSettings();

                    if (!empty($pluginSettings['enableRealtime'])) {
                        $realtimeRefreshInterval = Analytics::$plugin->getAnalytics()->getRealtimeRefreshInterval();

                        $widgetId = $this->id;
                        $widgetOptions = [
                            'viewId' => $widgetSettings['viewId'],
                            'refreshInterval' => $realtimeRefreshInterval,
                        ];

                        Craft::$app->getView()->registerAssetBundle(RealtimeReportWidgetAsset::class);

                        Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::$app->language.'";', true);

                        Craft::$app->getView()->registerJs('new Analytics.Realtime("widget'.$widgetId.'", '.Json::encode($widgetOptions).');');

                        return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/body');
                    } else {
                        return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/disabled');
                    }
                } else {
                    return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
                }
            } else {
                return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/disabled');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan()
    {
        return 1;
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        $settings = $this->getSettings();
        $reportingViews = Analytics::$plugin->getViews()->getViews();

        return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/settings', array(
            'settings' => $settings,
            'reportingViews' => $reportingViews,
        ));
    }
}
