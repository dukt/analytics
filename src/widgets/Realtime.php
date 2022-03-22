<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\Json;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\realtimereportwidget\RealtimeReportWidgetAsset;
use craft\web\View;

class Realtime extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    public $viewId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function isSelectable(): bool
    {
        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');

        if (!$plugin) {
            return false;
        }

        $settings = $plugin->getSettings();

        if (empty($settings['enableRealtime'])) {
            return false;
        }

        return parent::isSelectable();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('analytics', 'Active users');
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return Craft::getAlias('@dukt/analytics/icons/realtime-report.svg');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getBodyHtml(): ?string
    {
        $view = Craft::$app->getView();

        if (Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
            if (Analytics::$plugin->getSettings()->enableWidgets) {
                $reportingViews = Analytics::$plugin->getViews()->getViews();

                if ((array) $reportingViews !== []) {
                    $widgetSettings = $this->settings;

                    $reportingView = Analytics::$plugin->getViews()->getViewById($widgetSettings['viewId']);

                    if ($reportingView !== null) {
                        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
                        $pluginSettings = $plugin->getSettings();

                        if (!empty($pluginSettings['enableRealtime'])) {
                            $realtimeRefreshInterval = Analytics::$plugin->getAnalytics()->getRealtimeRefreshInterval();

                            $widgetId = $this->id;
                            $widgetOptions = [
                                'viewId' => $widgetSettings['viewId'],
                                'refreshInterval' => $realtimeRefreshInterval,
                            ];

                            $view->registerTranslations('analytics', [
                                'Minutes ago',
                                'Pageviews',
                                '{count} minute ago',
                                '{count} minutes ago',
                            ]);

                            $view->registerAssetBundle(RealtimeReportWidgetAsset::class);
                            $view->registerJs('var AnalyticsChartLanguage = "'.Craft::$app->language.'";', true);
                            $view->registerJs('new Analytics.Realtime("widget'.$widgetId.'", '.Json::encode($widgetOptions).');');

                            return $view->renderTemplate('analytics/_components/widgets/Realtime/body', [
                                'reportingView' => $reportingView
                            ]);
                        }

                        return $view->renderTemplate('analytics/_components/widgets/Realtime/disabled');
                    }

                    return $view->renderTemplate('analytics/_special/view-not-configured');
                }

                return $view->renderTemplate('analytics/_special/no-views');
            }

            return $view->renderTemplate('analytics/_components/widgets/Realtime/disabled');
        }

        return $view->renderTemplate('analytics/_special/not-connected');
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return 1;
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(): ?string
    {
        $settings = $this->getSettings();
        $reportingViews = Analytics::$plugin->getViews()->getViews();

        if ((array) $reportingViews !== []) {
            return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/settings', [
                'settings' => $settings,
                'reportingViews' => $reportingViews,
            ]);
        }

        return null;
    }
}
