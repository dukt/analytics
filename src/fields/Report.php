<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2019, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\helpers\Json;
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
    public function getInputHtml($value, ElementInterface $element = null): string
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

        if (!$siteView) {
            return $view->renderTemplate('analytics/_special/view-not-configured');
        }

        $reportingView = $siteView->getView();

        if (!$reportingView) {
            return $view->renderTemplate('analytics/_special/view-not-configured');
        }

        $variables = [
            'hasUrl' => false,
            'isNew' => false,
        ];

        if ($element) {
            // Reformat the input name into something that looks more like an ID
            $id = $view->formatInputId($name);

            // Figure out what that ID is going to look like once it has been namespaced
            $namespacedId = $view->namespaceInputId($id);

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
                    'localeDefinition' => Analytics::$plugin->getAnalytics()->getD3LocaleDefinition(['currency' => $reportingView->gaViewCurrency])
                ];


                // Add locale definition to JS options

                $siteView = Analytics::$plugin->getViews()->getSiteViewBySiteId($element->siteId);

                if ($siteView) {
                    $reportingView = $siteView->getView();

                    if ($reportingView) {
                        $jsOptions['localeDefinition'] = Analytics::$plugin->getAnalytics()->getD3LocaleDefinition(['currency' => $reportingView->gaViewCurrency]);
                    }
                }


                // Add cached response to JS options if any

                $cacheId = ['reports.getElementReport', $request];
                $response = Analytics::$plugin->cache->get($cacheId);

                if ($response) {
                    $response = [
                        'type' => 'area',
                        'chart' => $response
                    ];

                    $jsOptions['cachedResponse'] = $response;
                }


                // Register JS & Styles
                $view->registerJsFile('//www.gstatic.com/charts/loader.js', [
                    'position' => View::POS_HEAD,
                ]);
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
                    'element' => $element
                ];
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
