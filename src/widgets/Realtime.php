<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\Json;
use dukt\analytics\Plugin as Analytics;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;

class Realtime extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    public $sourceId;

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
     * @return string|null
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getBodyHtml(): ?string
    {
        $view = Craft::$app->getView();

        if (!Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
            return $view->renderTemplate('analytics/_special/not-connected');
        }

        if (!Analytics::$plugin->getSettings()->enableWidgets) {
            return $view->renderTemplate('analytics/_components/widgets/Realtime/disabled');
        }

        $sources = Analytics::$plugin->getSources()->getSources();

        if ((array) $sources === []) {
            return $view->renderTemplate('analytics/_special/no-sources');
        }

        $widgetSettings = $this->settings;

        $source = Analytics::$plugin->getSources()->getSourceById($widgetSettings['sourceId']);

        if ($source === null) {
            return $view->renderTemplate('analytics/_special/source-not-configured');
        }

        $plugin = Craft::$app->getPlugins()->getPlugin('analytics');
        $pluginSettings = $plugin->getSettings();

        if (empty($pluginSettings['enableRealtime'])) {
            return $view->renderTemplate('analytics/_components/widgets/Realtime/disabled');
        }

        $realtimeRefreshInterval = Analytics::$plugin->getAnalytics()->getRealtimeRefreshInterval();

        $view->registerTranslations('analytics', [
            'Minutes ago',
            'Pageviews',
            '{count} minute ago',
            '{count} minutes ago',
            'Active users per minute',
            'Active pages',
            'Page',
            'Users',
        ]);

        $variables = [
            'id' => $this->id,
            'source' => $source,
            'refreshInterval' => $realtimeRefreshInterval,
        ];

        $view->registerAssetBundle(AnalyticsAsset::class);
        $view->registerJs('new AnalyticsVueRealtimeWidget({data: {pluginOptions: '.Json::encode($variables).'}}).$mount("#analytics-widget-'.$this->id.'");;');

        return $view->renderTemplate('analytics/_components/widgets/Realtime/body', $variables);
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
        $sources = Analytics::$plugin->getSources()->getSources();

        if ((array) $sources !== []) {
            return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Realtime/settings', [
                'settings' => $settings,
                'sources' => $sources,
            ]);
        }

        return null;
    }
}
