<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportFieldType extends BaseFieldType
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Analytics Report');
    }

    /**
     * @inheritDoc IFieldType::defineContentAttribute()
     *
     * @return mixed
     */
    public function defineContentAttribute()
    {
        return AttributeType::String;
    }

    /**
     * Show field
     */
    public function getInputHtml($name, $value)
    {
        if (craft()->analytics->checkPluginRequirements()) {
            if (craft()->config->get('enableFieldtype', 'analytics')) {
                // Reformat the input name into something that looks more like an ID
                $id = craft()->templates->formatInputId($name);

                // Figure out what that ID is going to look like once it has been namespaced
                $namespacedId = craft()->templates->namespaceInputId($id);

                if ($this->element->uri) {
                    $uri = craft()->analytics->getElementUrlPath($this->element->id, $this->element->locale);

                    $startDate = date('Y-m-d', strtotime('-1 month'));
                    $endDate = date('Y-m-d');
                    $metrics = 'ga:pageviews';
                    $dimensions = 'ga:date';

                    $optParams = [
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    ];

                    $criteria = new Analytics_RequestCriteriaModel;
                    $criteria->startDate = $startDate;
                    $criteria->endDate = $endDate;
                    $criteria->metrics = $metrics;
                    $criteria->optParams = $optParams;

                    $options = [];

                    $cacheId = [
                        'ReportsController.actionGetElementReport',
                        $criteria->getAttributes()
                    ];
                    $response = craft()->analytics_cache->get($cacheId);

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

                    $apiKey = craft()->config->get('apiKey', 'analytics');

                    craft()->templates->includeJsFile('https://www.google.com/jsapi'.($apiKey ? '?key='.$apiKey : ''), true);
                    craft()->templates->includeJsResource('analytics/js/ReportField.js');
                    craft()->templates->includeCssResource('analytics/css/ReportField.css');

                    craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
                    craft()->templates->includeJs('new AnalyticsReportField("'.$namespacedId.'-field", '.$jsonOptions.');');

                    $variables = [
                        'isNew' => false,
                        'hasUrl' => true,
                        'id' => $id,
                        'uri' => $uri,
                        'name' => $name,
                        'value' => $value,
                        'model' => $this->model,
                        'element' => $this->element
                    ];
                } elseif (!$this->element->id) {
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

                return craft()->templates->render('analytics/_components/fieldtypes/Report/input', $variables);
            } else {
                return craft()->templates->render('analytics/_components/fieldtypes/Report/disabled');
            }
        } else {
            return craft()->templates->render('analytics/_special/plugin-not-configured');
        }
    }
}
