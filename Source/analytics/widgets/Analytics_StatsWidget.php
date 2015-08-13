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
        $settings = $this->getSettings();
        $dataSourceClassName = 'GoogleAnalytics';
        $dataSource = craft()->analytics->getDataSource($dataSourceClassName);
        $inject = $dataSource->getSettingsHtml([
            'settings' => $settings
        ]);

        return craft()->templates->render('analytics/widgets/stats/settings', array(
           'settings' => $settings,
           'inject' => $inject,
        ));
    }

    public function getBodyHtml()
    {
        $widgets = craft()->dashboard->getUserWidgets();
        $requiredChartTypes = [];

        foreach($widgets as $widget)
        {
            if($widget->type == 'Analytics_Stats')
            {
                $settings = $widget->settings;
                $requiredChartTypes[] = $settings['chart'];
            }
        }

        craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
        craft()->templates->includeJsResource('analytics/lib/jquery.serializeJSON/jquery.serializejson.min.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsStats.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsStats.css');


        // build request from db or default

        $cachedRequest = array(
            'chart' => $settings['chart'],
            'period' => $settings['period'],
            'options' => $settings['options'],
        );

        $dataSourceClassName = 'GoogleAnalytics';
        $dataSource = craft()->analytics->getDataSource($dataSourceClassName);
        $cachedResponse = $dataSource->getChartData($cachedRequest);

        $cachedResponse['request'] = $cachedRequest;

        $options = array(
            'cachedRequest' => $cachedRequest,
            'cachedResponse' => $cachedResponse,
        );

        $widgetId = $this->model->id;
        $jsonOptions = json_encode($options);

        $jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}";';
        $jsTemplate .= 'window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
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
            'colspan' => array(AttributeType::Number, 'default' => 2),
            'realtime' => array(AttributeType::Bool),
            'chart' => array(AttributeType::String),
            'period' => array(AttributeType::String),
            'options' => array(AttributeType::Mixed),
        );
    }
}
