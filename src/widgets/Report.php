<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\widgets;

use Craft;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\Plugin as Analytics;

class Report extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $viewId;

    /**
     * @var bool|null
     */
    public $realtime;

    /**
     * @var string|null
     */
    public $chart;

    /**
     * @var string|null
     */
    public $period;

    /**
     * @var array|null
     */
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
    public function getTitle(): ?string
    {
        $reportTitle = $this->_getReportTitle();

        if ($reportTitle) {
            return $reportTitle;
        }

        return Craft::t('analytics', 'Analytics Report');
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
     */
    public function getBodyHtml(): ?string
    {
        $view = Craft::$app->getView();

        try {
            if (!Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
                return $view->renderTemplate('analytics/_special/not-connected');
            }

            if (!Analytics::$plugin->getSettings()->enableWidgets) {
                return $view->renderTemplate('analytics/_components/widgets/Report/disabled');
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

            $request = [
                'viewId' => $widgetSettings['viewId'] ?? null,
                'chart' => $widgetSettings['chart'] ?? null,
                'period' => $widgetSettings['period'] ?? null,
                'options' => $widgetSettings['options'][$widgetSettings['chart']] ?? null,
            ];

            // use cached response if available
            if (Analytics::$plugin->getSettings()->enableCache === true) {
                $cacheId = ['getReport', $request];
                $cachedResponse = Analytics::$plugin->getCache()->get($cacheId);
            }


            // render
            $jsOptions = [
                'currencyDefinition' => Analytics::$plugin->getAnalytics()->getCurrencyDefinition($reportingView->gaCurrency),
                'chartLanguage' => Analytics::$plugin->getAnalytics()->getChartLanguage(),
                'request' => $request,
                'cachedResponse' => $cachedResponse ?? null,
            ];

            $view->registerAssetBundle(AnalyticsAsset::class);

            $view->registerJs('new AnalyticsVueReportWidget({data: {pluginOptions: '.Json::encode($jsOptions).'}}).$mount("#analytics-widget-'.$this->id.'");;');

            return $view->renderTemplate('analytics/_components/widgets/Report/body', [
                'id' => $this->id
            ]);
        } catch (\Exception $exception) {
            Craft::error('Couldn’t load report widget: '.$exception->getMessage(). " ".$exception->getTraceAsString(), __METHOD__);
            return $view->renderTemplate('analytics/_special/error');
        }
    }

    /**
     * ISavableComponentType::getSettingsHtml()
     *
     * @return null|string
     * @throws \Twig\Error\LoaderError
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getSettingsHtml(): ?string
    {
        Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

        $reportingViews = Analytics::$plugin->getViews()->getViews();

        if ((array) $reportingViews !== []) {
            $randomString = StringHelper::randomString();
            $id = 'analytics-settings-'.$randomString;
            $vueId = 'vue-analytics-settings-'.$randomString;
            $namespaceId = Craft::$app->getView()->namespaceInputId($id);
            $vueNamespaceId = Craft::$app->getView()->namespaceInputId($vueId);

            // Settings
            $settings = $this->getSettings();

            $variables = [
                'id' => $id,
                'namespaceId' => $namespaceId,
                'vueNamespaceId' => $vueNamespaceId,
                'settings' => $settings,
                'reportingViews' => $reportingViews,
            ];

            $vueVariables = [
                'id' => $id,
                'namespaceId' => $namespaceId,
                'namespace' => Craft::$app->getView()->getNamespace(),
                'vueNamespaceId' => $vueNamespaceId,
                'settings' => $settings,
                'reportingViews' => $reportingViews,
            ];

            $vueJsonOptions = Json::encode($vueVariables);

            Craft::$app->getView()->registerJs('new AnalyticsVueReportWidgetSettings({data: {pluginSettings: '.$vueJsonOptions.'}}).$mount("#'.$vueNamespaceId.'");');

            return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Report/settings', $variables);
        }

        return null;
    }

    // Private Methods
    // =========================================================================

    /**
     * Returns the title of the report
     *
     * @return string|null
     */
    private function _getReportTitle()
    {
        try {
            $name = [];
            $chartType = $this->settings['chart'];

            if (isset($this->settings['options'][$chartType])) {
                $options = $this->settings['options'][$chartType];

                if (!empty($options['dimension'])) {
                    $name[] = Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($options['dimension']));
                }

                if (!empty($options['metric'])) {
                    $name[] = Craft::t('analytics', Analytics::$plugin->getMetadata()->getDimMet($options['metric']));
                }
            }

            if (!empty($this->settings['period'])) {
                $name[] = Craft::t('analytics', ucfirst($this->settings['period']));
            }

            if (count($name) > 0) {
                return implode(' - ', $name);
            }
        } catch (\Exception $exception) {
            Craft::info('Couldn’t get Analytics Report’s title: '.$exception->getMessage(), __METHOD__);
        }

        return null;
    }
}
