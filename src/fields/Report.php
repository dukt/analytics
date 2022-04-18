<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\helpers\Json;
use dukt\analytics\web\assets\analyticsvue\AnalyticsVueAsset;
use dukt\analytics\web\assets\reportfield\ReportFieldAsset;
use dukt\analytics\Plugin as Analytics;
use craft\web\View;

class Report extends Field
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('analytics', 'Analytics Report');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        $view = Craft::$app->getView();
        $name = $this->handle;

        if (!Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
            return $view->renderTemplate('analytics/_special/plugin-not-configured');
        }

        if (!Analytics::$plugin->getSettings()->enableFieldtype) {
            return $view->renderTemplate('analytics/_components/fieldtypes/Report/disabled');
        }

        $siteView = Analytics::$plugin->getViews()->getSiteViewBySiteId($element->siteId);

        if (!$siteView instanceof \dukt\analytics\models\SiteView) {
            return $view->renderTemplate('analytics/_special/view-not-configured');
        }

        $reportingView = $siteView->getView();

        if (!$reportingView instanceof \dukt\analytics\models\View) {
            return $view->renderTemplate('analytics/_special/view-not-configured');
        }

        // Reformat the input name into something that looks more like an ID
        $id = $view->formatInputId($name);

        // Figure out what that ID is going to look like once it has been namespaced
        $namespacedId = $view->namespaceInputId($id);

        $variables = [
            'hasUrl' => false,
            'isNew' => false,
        ];

        if ($element !== null) {
            if ($element->id && $element->uri) {
                $uri = Analytics::$plugin->getAnalytics()->getElementUrlPath($element->id, $element->siteId);

                $startDate = date('Y-m-d', strtotime('-1 month'));
                $endDate = date('Y-m-d');
                $metrics = 'ga:pageviews';
                $dimensions = 'ga:date';
                $filters = 'ga:pagePath=='.$uri;

                $request = [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'metrics' => $metrics,
                    'dimensions' => $dimensions,
                    'filters' => $filters
                ];

                // JS Options
                $jsOptions = [
                    'chartLanguage' => Analytics::$plugin->getAnalytics()->getChartLanguage(),
                ];

                // Add locale definition to JS options
                $siteView = Analytics::$plugin->getViews()->getSiteViewBySiteId($element->siteId);

                if ($siteView !== null) {
                    $reportingView = $siteView->getView();

                    if ($reportingView !== null) {
                        // Currency definition
                        $jsOptions['currencyDefinition'] = Analytics::$plugin->getAnalytics()->getCurrencyDefinition($reportingView->gaViewCurrency);
                    }
                }

                // Add cached response to JS options if any
                $cacheId = ['reports.getElementReport', $request];
                $response = Analytics::$plugin->getCache()->get($cacheId);

                if ($response) {
                    $response = [
                        'type' => 'area',
                        'chart' => $response
                    ];

                    $jsOptions['cachedResponse'] = $response;
                }

                // Register JS & Styles
                $view->registerAssetBundle(ReportFieldAsset::class);
                $view->registerJs('new AnalyticsReportField("'.$namespacedId.'-field", '.Json::encode($jsOptions).');');

                // Variables
                $variables = [
                    'isNew' => false,
                    'hasUrl' => true,
                    'id' => $id,
                    'uri' => $uri,
                    'name' => $name,
                    'value' => $value,
                    'model' => $this,
                    'element' => $element,
                    'namespacedId' => $namespacedId,
                ];
                $view->registerAssetBundle(AnalyticsVueAsset::class);
                $view->registerJs('new AnalyticsVueReportField({data: {pluginOptions: '.Json::encode($variables).'}}).$mount("#fields-vue-'.$namespacedId.'");;');
            } elseif (!$element->id) {
                $variables = [
                    'hasUrl' => false,
                    'isNew' => true,
                ];
            }
        }

        return $view->renderTemplate('analytics/_components/fieldtypes/Report/input', $variables);
    }
}
