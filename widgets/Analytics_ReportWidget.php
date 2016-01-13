<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportWidget extends BaseWidget
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
     * @inheritDoc IWidget::getTitle()
     *
     * @return string
     */
    public function getTitle()
    {
        try {
            $name = [];
            $chartType = $this->settings['chart'];

            if(isset($this->settings['options'][$chartType]))
            {
                $options = $this->settings['options'][$chartType];

                if(!empty($options['dimension']))
                {
                    $name[] = Craft::t(craft()->analytics_meta->getDimMet($options['dimension']));
                }

                if(!empty($options['metric']))
                {
                    $name[] = Craft::t(craft()->analytics_meta->getDimMet($options['metric']));
                }
            }

            if(count($name) > 0)
            {
                return implode(" - ", $name);
            }
        }
        catch(\Exception $e)
        {
            // todo: error handling
        }

        return Craft::t('Analytics Report');
    }

    /**
     * @inheritDoc IWidget::getIconPath()
     *
     * @return string
     */
    public function getIconPath()
    {
        return craft()->resources->getResourcePath('analytics/images/widgets/stats.svg');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        if(craft()->analytics_plugin->checkRequirements())
        {
            if(craft()->config->get('enableWidgets', 'analytics'))
            {
                $settings = $this->settings;

                $profileId = craft()->analytics->getProfileId();

                if($profileId)
                {
                    craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
                    craft()->templates->includeJsResource('analytics/js/Analytics.js');
                    craft()->templates->includeJsResource('analytics/js/AnalyticsReportWidgetSettings.js');
                    craft()->templates->includeJsResource('analytics/js/AnalyticsReportWidget.js');

                    craft()->templates->includeCssResource('analytics/css/AnalyticsReportWidget.css');
                    craft()->templates->includeCssResource('analytics/css/AnalyticsReportWidgetSettings.css');


                    $options = [];

                    // request

                    $options['request'] = array(
                        'chart' => (isset($settings['chart']) ? $settings['chart'] : null),
                        'period' => (isset($settings['period']) ? $settings['period'] : null),
                        'options' => (isset($settings['options'][$settings['chart']]) ? $settings['options'][$settings['chart']] : null),
                    );


                    // cached response

                    if(craft()->config->get('enableCache', 'analytics') === true)
                    {
                        $cacheId = ['getChartData', $options['request'], $profileId];
                        $cachedResponse = craft()->analytics_cache->get($cacheId);

                        if($cachedResponse)
                        {
                            $options['cachedResponse'] = $cachedResponse;
                        }
                    }


                    // settings modal

                    $widgetId = $this->model->id;
                    $jsonOptions = json_encode($options);

                    $jsTemplate = 'window.csrfTokenName = "{{ craft.config.csrfTokenName|e(\'js\') }}";';
                    $jsTemplate .= 'window.csrfTokenValue = "{{ craft.request.csrfToken|e(\'js\') }}";';
                    $js = craft()->templates->renderString($jsTemplate);
                    craft()->templates->includeJs($js);

                    craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
                    craft()->templates->includeJs('new Analytics.ReportWidget("widget'.$widgetId.'", '.$jsonOptions.');');

                    return craft()->templates->render('analytics/_components/widgets/Report/body');
                }
                else
                {
                    return craft()->templates->render('analytics/_install/plugin-not-configured');
                }
            }
            else
            {
                return craft()->templates->render('analytics/_components/widgets/Report/disabled');
            }
        }
        else
        {
            return craft()->templates->render('analytics/_install/plugin-not-configured');
        }
    }

    /**
     * @inheritDoc ISavableComponentType::getSettingsHtml()
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        craft()->templates->includeJsResource('analytics/js/Analytics.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsReportWidgetSettings.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsReportWidgetSettings.css');

        $id = 'analytics-settings-'.StringHelper::randomString();
        $namespaceId = craft()->templates->namespaceInputId($id);

        craft()->templates->includeJs("new Analytics.ReportWidgetSettings('".$namespaceId."');");

        $settings = $this->getSettings();

        try {
            $dataSource = craft()->analytics->getDataSource();

            $inject = $dataSource->getSettingsHtml([
                'settings' => $settings
            ]);
        }
        catch(\Exception $e)
        {
            // todo: exception handling

            $inject = null;
        }

        return craft()->templates->render('analytics/_components/widgets/Report/settings', array(
           'id' => $id,
           'settings' => $settings,
           'inject' => $inject,
        ));
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
            'realtime' => array(AttributeType::Bool),
            'chart' => array(AttributeType::String),
            'period' => array(AttributeType::String),
            'options' => array(AttributeType::Mixed),
        );
    }
}
