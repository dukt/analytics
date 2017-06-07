<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use dukt\analytics\web\assets\reportfield\ReportFieldAsset;
use dukt\analytics\Plugin as Analytics;

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
        $name = $this->handle;

        if (Analytics::$plugin->getAnalytics()->checkPluginRequirements()) {
            if (Analytics::$plugin->getSettings()->enableFieldtype) {
                $plugin = Craft::$app->getPlugins()->getPlugin('analytics');

                // Reformat the input name into something that looks more like an ID
                $id = Craft::$app->getView()->formatInputId($name);

                // Figure out what that ID is going to look like once it has been namespaced
                $namespacedId = Craft::$app->getView()->namespaceInputId($id);

                if ($element->uri) {
                    $uri = Analytics::$plugin->getAnalytics()->getElementUrlPath($element->id, $element->siteId);

                    $startDate = date('Y-m-d', strtotime('-1 month'));
                    $endDate = date('Y-m-d');
                    $metrics = 'ga:pageviews';
                    $dimensions = 'ga:date';
                    $filters = "ga:pagePath==".$uri;

                    $request = [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'metrics' => $metrics,
                        'dimensions' => $dimensions,
                        'filters' => $filters
                    ];

                    // Check if there is a cached response and add it to JS options if so

                    $cacheId = ['reports.getElementReport', $request];
                    $response = Analytics::$plugin->cache->get($cacheId);

                    $options = [];

                    if ($response) {
                        $response = [
                            'type' => 'area',
                            'chart' => $response
                        ];

                        $options = [
                            'cachedResponse' => $response
                        ];
                    }

                    $jsonOptions = json_encode($options);

                    Craft::$app->getView()->registerAssetBundle(ReportFieldAsset::class);

                    Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::t('analytics', 'analyticsChartLanguage').'";');
                    Craft::$app->getView()->registerJs('new AnalyticsReportField("'.$namespacedId.'-field", '.$jsonOptions.');');

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
                } else {
                    $variables = [
                        'hasUrl' => false,
                        'isNew' => false,
                    ];
                }

                return Craft::$app->getView()->renderTemplate('analytics/_components/fieldtypes/Report/input', $variables);
            }

            return Craft::$app->getView()->renderTemplate('analytics/_components/fieldtypes/Report/disabled');
        }

        return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
    }
}
