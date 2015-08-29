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

    /**
     * @inheritDoc IWidget::getTitle()
     *
     * @return string
     */
    public function getTitle()
    {
        $name = [];

        if(!empty($this->settings['options']['dimension']))
        {
            $name[] = craft()->analytics_meta->getDimMet($this->settings['options']['dimension']);
        }

        if(!empty($this->settings['options']['metric']))
        {
            $name[] = craft()->analytics_meta->getDimMet($this->settings['options']['metric']);
        }

        if(count($name) > 0)
        {
            return implode(" - ", $name);
        }

        return Craft::t('Analytics Stats');
    }

    public function getBodyHtml()
    {
        $settings = $this->settings;

        craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
        craft()->templates->includeJsResource('analytics/lib/jquery.serializeJSON/jquery.serializejson.min.js');
        craft()->templates->includeJsResource('analytics/js/Analytics.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsStatsWidget.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsStatsWidget.css');


        $options = [];

        // request

        $options['request'] = array(
            'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
            'period' => (isset($settings['period']) ? $settings['period'] : null),
            'options' => (isset($settings['options']) ? $settings['options'] : null),
            'colspan' => (isset($settings['colspan']) ? $settings['colspan'] : null),
        );


        // cached response

        $profileId = craft()->analytics->getProfileId();

        $cacheKey = craft()->analytics->getCacheKey('getChartData', [$options['request'], $profileId]);

        $cachedResponse = craft()->cache->get($cacheKey);

        if($cachedResponse)
        {
            $options['cachedResponse'] = $cachedResponse;
        }


        // settings modal

        $dataSource = craft()->analytics->getDataSource();
        $inject = $dataSource->getSettingsHtml([
            'settings' => $this->settings,
        ]);

        $options['settingsModalTemplate'] = craft()->templates->render('analytics/widgets/stats/settingsModal', array(
            'settings' => $this->settings,
            'inject' => $inject,
        ));

        //-----------------------------------------------

        $widgetId = $this->model->id;
        $jsonOptions = json_encode($options);

        $jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}";';
        $jsTemplate .= 'window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
        $js = craft()->templates->renderString($jsTemplate);
        craft()->templates->includeJs($js);

        craft()->templates->includeJs('new Analytics.Stats("widget'.$widgetId.'", '.$jsonOptions.');');

        return craft()->templates->render('analytics/widgets/stats');
    }

    public function getSettingsHtml()
    {
        $settings = $this->getSettings();

        $dataSource = craft()->analytics->getDataSource();
        $inject = $dataSource->getSettingsHtml([
            'settings' => $settings
        ]);

        return craft()->templates->render('analytics/widgets/stats/settings', array(
           'settings' => $settings,
           'inject' => $inject,
        ));
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
