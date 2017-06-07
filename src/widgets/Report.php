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

class Report extends \craft\base\Widget
{
    // Properties
    // =========================================================================

    public $viewId;
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
        $view = Craft::$app->getView();

        try {
            if (Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
                if (Analytics::$plugin->getSettings()->enableWidgets) {
                    $reportingViews = Analytics::$plugin->getViews()->getViews();

                    if(count($reportingViews) > 0) {
                        $widgetSettings = $this->settings;

                        $reportingView = Analytics::$plugin->getViews()->getViewById($widgetSettings['viewId']);

                        if ($reportingView) {
                            $request = [
                                'viewId' => (isset($widgetSettings['viewId']) ? $widgetSettings['viewId'] : null),
                                'chart' => (isset($widgetSettings['chart']) ? $widgetSettings['chart'] : null),
                                'period' => (isset($widgetSettings['period']) ? $widgetSettings['period'] : null),
                                'options' => (isset($widgetSettings['options'][$widgetSettings['chart']]) ? $widgetSettings['options'][$widgetSettings['chart']] : null),
                            ];


                            // use cached response if available

                            if (Analytics::$plugin->getSettings()->enableCache === true) {
                                $cacheId = ['getReport', $request];
                                $cachedResponse = Analytics::$plugin->cache->get($cacheId);
                            }


                            // render

                            $widgetId = $this->id;

                            $widgetOptions = [
                                'request' => $request,
                                'cachedResponse' => isset($cachedResponse) ? $cachedResponse : null,
                            ];

                            $view->registerAssetBundle(ReportWidgetAsset::class);

                            $jsTemplate = 'window.csrfTokenName = "{{ craft.app.config.general.csrfTokenName|e(\'js\') }}";';
                            $jsTemplate .= 'window.csrfTokenValue = "{{ craft.app.request.csrfToken|e(\'js\') }}";';
                            $js = $view->renderString($jsTemplate);

                            $view->registerJs($js);
                            $view->registerJs('var AnalyticsChartLanguage = "'.Craft::t('analytics', 'analyticsChartLanguage').'";');
                            $view->registerJs('new Analytics.ReportWidget("widget'.$widgetId.'", '.Json::encode($widgetOptions).');');

                            return $view->renderTemplate('analytics/_components/widgets/Report/body');
                        }

                        return $view->renderTemplate('analytics/_special/view-not-configured');
                    }

                    return $view->renderTemplate('analytics/_special/no-views');
                }

                return $view->renderTemplate('analytics/_components/widgets/Report/disabled');
            }

            return $view->renderTemplate('analytics/_special/not-connected');

        } catch(\Exception $e) {
            return $view->renderTemplate('analytics/_special/not-connected');
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

        $reportingViews = Analytics::$plugin->getViews()->getViews();

        if(count($reportingViews) > 0) {
            $id = 'analytics-settings-'.StringHelper::randomString();
            $namespaceId = Craft::$app->getView()->namespaceInputId($id);

            Craft::$app->getView()->registerJs("new Analytics.ReportWidgetSettings('".$namespaceId."');");

            $settings = $this->getSettings();


            // select options

            $chartTypes = ['area', 'counter', 'pie', 'table', 'geo'];

            $selectOptions = [];

            foreach ($chartTypes as $chartType) {
                $selectOptions[$chartType] = $this->_geSelectOptionsByChartType($chartType);
            }

            // view options

            return Craft::$app->getView()->renderTemplate('analytics/_components/widgets/Report/settings', [
                'id' => $id,
                'settings' => $settings,
                'selectOptions' => $selectOptions,
                'reportingViews' => $reportingViews,
            ]);
        }
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
                    $name[] = Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($options['dimension']));
                }

                if(!empty($options['metric']))
                {
                    $name[] = Craft::t('analytics', Analytics::$plugin->metadata->getDimMet($options['metric']));
                }
            }

            if(!empty($this->settings['period']))
            {
                $name[] = Craft::t('analytics', ucfirst($this->settings['period']));
            }

            if(count($name) > 0)
            {
                return implode(" - ", $name);
            }
        }
        catch(\Exception $e)
        {
            Craft::info('Couldn’t get Analytics Report’s title: '.$e->getMessage(), __METHOD__);
        }
    }
}
