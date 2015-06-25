<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_StatsWidget extends BaseWidget
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
        return Craft::t('Analytics Stats');
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('analytics/widgets/stats/settings', array(
           'settings' => $this->getSettings()
        ));
    }

    public function getBodyHtml()
    {
        craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
        craft()->templates->includeJsResource('analytics/js/AnalyticsStats.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsStats.css');

        $request = array(
            'chart' => 'area',
            'metric' => 'ga:pageviews',
            'period' => 'week'
        );

        $cachedResponse = craft()->analytics->getChartData($request);
        $cachedResponse['request'] = $request;

        $options = array(
            'cachedResponse' => $cachedResponse,
        );

        $widgetId = $this->model->id;
        $jsonOptions = json_encode($options);

        $jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}"; window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
        $js = craft()->templates->renderString($jsTemplate);
        craft()->templates->includeJs($js);

        craft()->templates->includeJs('new Analytics.Stats("widget'.$widgetId.'", '.$jsonOptions.');');

        return craft()->templates->render('analytics/widgets/stats');
    }

    public function getColspan()
    {
        $settings = $this->getSettings();

        if(isset($settings->colspan))
        {
            if($settings->colspan > 0)
            {
                return $settings->colspan;
            }
        }

        return 1;
    }

    // Protected Methods
    // =========================================================================

    protected function defineSettings()
    {
        return array(
           'menu' => array(AttributeType::String),
           'dimension' => array(AttributeType::String),
           'metric' => array(AttributeType::String),
           'chart' => array(AttributeType::String),
           'chart' => array(AttributeType::String),
           'period' => array(AttributeType::String),
           'pinned' => array(AttributeType::Bool),
           'colspan' => array(AttributeType::Number, 'default' => 2)
        );
    }
}