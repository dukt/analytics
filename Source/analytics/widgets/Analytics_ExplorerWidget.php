<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ExplorerWidget extends BaseWidget
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
        return Craft::t('Analytics');
    }

    /**
     * @inheritDoc IWidget::getTitle()
     *
     * @return string
     */
    public function getTitle()
    {
        $widget = $this->model;
        $settings = $widget->settings;

        $browserSelect = craft()->analytics->getBrowserSelect();
        $optgroup = false;

        foreach($browserSelect as $option)
        {
            if(isset($option['optgroup']))
            {
                $optgroup = $option['optgroup'];
            }

            if(isset($option['value']))
            {
                if($option['value'] == $settings['menu'])
                {
                    $title = "";

                    if($optgroup)
                    {
                        $title .= $optgroup.' / ';
                    }

                    $title .= $option['label'];

                    return Craft::t($title);
                }
            }
        }

        return Craft::t("Audience / Overview");
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('analytics/widgets/explorer/settings', array(
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
            return craft()->templates->render('analytics/widgets/explorer/disabled', array());
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

        $settings = json_encode($settings);


        // js
        craft()->templates->includeJs('var AnalyticsChartLanguage = "'.craft()->analytics->getLanguage().'";', true);
        craft()->templates->includeJs('var AnalyticsRealtimeInterval = "'.$pluginSettings->realtimeRefreshInterval.'";', true);
        craft()->templates->includeJs('var AnalyticsBrowserSections = '.$browserSectionsJson.';');
        craft()->templates->includeJs('var AnalyticsBrowserData = '.$browserDataJson.';');
        craft()->templates->includeJs('new Analytics.Explorer("widget'.$widget->id.'", '.$settings.');');

        // render
        $variables['browserSections'] = $browserSections;
        $variables['browserSelect'] = $browserSelect;
        $variables['widget'] = $widget;
        $variables['pluginSettings'] = $pluginSettings;


        // render template

        return craft()->templates->render('analytics/widgets/explorer', $variables);
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