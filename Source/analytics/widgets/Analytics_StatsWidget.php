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
        $disableAnalytics = false;

        if(craft()->config->get('disableAnalytics') === null)
        {
            if(craft()->config->get('disableAnalytics', 'analytics') === true)
            {
                $disableAnalytics = true;
            }
        }
        else
        {
            if(craft()->config->get('disableAnalytics') === true)
            {
                $disableAnalytics = true;
            }
        }

        if($disableAnalytics)
        {
            return craft()->templates->render('analytics/widgets/stats/disabled', array());
        }

        $plugin = craft()->plugins->getPlugin('analytics');

        // settings
        $pluginSettings = $plugin->getSettings();

        // widget
        $widget = $this->model;


        // get data

        $browserSections = craft()->analytics->getBrowserSections();
        $browserSectionsJson = craft()->analytics->getBrowserSections(true);
        $browserData = craft()->analytics->getBrowserData();
        $browserDataJson = craft()->analytics->getBrowserData(true);
        $browserSelect = craft()->analytics->getBrowserSelect();


        // settings

        $settings = array();

        foreach($widget->settings as $k => $v)
        {
            if(!empty($v))
            {
                $settings[$k] = $v;
            }
        }

        $jsonSettings = json_encode($settings);

        $chartData = craft()->analytics->getChartData($settings);

        // js
        craft()->templates->includeJs('var AnalyticsChartLanguage = "'.craft()->analytics->getLanguage().'";', true);
        craft()->templates->includeJs('var AnalyticsRealtimeInterval = "'.$pluginSettings->realtimeRefreshInterval.'";', true);
        craft()->templates->includeJs('var AnalyticsBrowserSections = '.$browserSectionsJson.';');
        craft()->templates->includeJs('var AnalyticsBrowserData = '.$browserDataJson.';');
        craft()->templates->includeJs('new Analytics.Explorer("widget'.$widget->id.'", '.$jsonSettings.', '.json_encode($chartData).');');

        // render
        $variables['browserSections'] = $browserSections;
        $variables['browserSelect'] = $browserSelect;
        $variables['widget'] = $widget;
        $variables['pluginSettings'] = $pluginSettings;


        // render template

        return craft()->templates->render('analytics/widgets/stats', $variables);
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