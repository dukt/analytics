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

class Ecommerce extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $viewId;

    /**
     * @var string|null
     */
    public $period;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('analytics', 'E-commerce');
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return Craft::getAlias('@dukt/analytics/icons/report.svg');
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
            return $view->renderTemplate('analytics/_components/widgets/Ecommerce/disabled');
        }

        $reportingViews = Analytics::$plugin->getViews()->getViews();

        if ((array) $reportingViews === []) {
            return $view->renderTemplate('analytics/_special/no-views');
        }

        $widgetSettings = $this->settings;
        $reportingView = Analytics::$plugin->getViews()->getViewById($widgetSettings['viewId']);

        if (!$reportingView instanceof \dukt\analytics\models\View) {
            return $view->renderTemplate('analytics/_special/view-not-configured');
        }

        $widgetId = $this->id;
        $widgetSettings = $this->settings;

        $widgetOptions = [
            'viewId' => $widgetSettings['viewId'],
            'period' => $widgetSettings['period'] ?? null,
            'currencyDefinition' => Analytics::$plugin->getAnalytics()->getCurrencyDefinition($reportingView->gaCurrency),
            'chartLanguage' => Analytics::$plugin->getAnalytics()->getChartLanguage(),
        ];

        $view->registerAssetBundle(AnalyticsAsset::class);
        $view->registerJs('new AnalyticsVueEcommerceWidget({data: {pluginOptions: '.Json::encode($widgetOptions).'}}).$mount("#analytics-widget-'.$widgetId.'");;');

        return $view->renderTemplate('analytics/_components/widgets/Ecommerce/body', [
            'id' => $this->id,
            'widgetSettings' => $widgetSettings
        ]);
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
            return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Ecommerce/settings', [
                'settings' => $settings,
                'reportingViews' => $reportingViews,
            ]);
        }

        return null;
    }
}
