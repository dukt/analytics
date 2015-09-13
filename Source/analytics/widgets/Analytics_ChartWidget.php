<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ChartWidget extends BaseWidget
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
        return Craft::t('Analytics Chart');
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

        return Craft::t('Analytics Chart');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        $settings = $this->settings;

        craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
        craft()->templates->includeJsResource('analytics/lib/jquery.serializeJSON/jquery.serializejson.min.js');
        craft()->templates->includeJsResource('analytics/js/Analytics.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsChartWidgetSettings.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsChartWidget.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsChartWidget.css');
        craft()->templates->includeCssResource('analytics/css/AnalyticsChartWidgetSettings.css');


        $options = [];

        // request

        $options['request'] = array(
            'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
            'period' => (isset($settings['period']) ? $settings['period'] : null),
            'options' => (isset($settings['options']) ? $settings['options'] : null),
            'colspan' => (isset($settings['colspan']) ? $settings['colspan'] : null),
        );


        // cached response

        if(craft()->config->get('enableCache', 'analytics') === true)
        {
            $profileId = craft()->analytics->getProfileId();


            $cacheId = ['getChartData', $options['request'], $profileId];
            $cachedResponse = craft()->analytics_cache->get($cacheId);

            if($cachedResponse)
            {
                $options['cachedResponse'] = $cachedResponse;
            }
        }


        // settings modal

        $dataSource = craft()->analytics->getDataSource();
        $inject = $dataSource->getSettingsHtml([
            'settings' => $this->settings,
        ]);

        $options['settingsModalTemplate'] = craft()->templates->render('analytics/_components/widgets/Chart/settingsModal', array(
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

        craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
        craft()->templates->includeJs('new Analytics.ChartWidget("widget'.$widgetId.'", '.$jsonOptions.');');

        return craft()->templates->render('analytics/_components/widgets/Chart/body');
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        craft()->templates->includeJsResource('analytics/js/Analytics.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsChartWidgetSettings.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsChartWidgetSettings.css');

        craft()->templates->includeJs("
            new Analytics.ChartWidgetSettings($('#content form'), {
                onSubmit: function(ev)
                {
                    ev.preventDefault();

                    var hiddenElements = $('input[type=text], textarea, select', this.\$form).filter(':hidden').filter(':not(.chart-select select)');
                    hiddenElements.remove();

                    ev.currentTarget.submit();
                }
            });
        ");

        $settings = $this->getSettings();

        $dataSource = craft()->analytics->getDataSource();
        $inject = $dataSource->getSettingsHtml([
            'settings' => $settings
        ]);

        return craft()->templates->render('analytics/_components/widgets/Chart/settings', array(
           'settings' => $settings,
           'inject' => $inject,
        ));
    }

    /**
     * @inheritDoc IWidget::getColspan()
     *
     * @return int
     */
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

    /**
     * @inheritDoc BaseSavableComponentType::defineSettings()
     *
     * @return array
     */
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
