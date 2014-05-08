<?php

namespace Craft;

class Analytics_CustomReportWidget extends BaseWidget
{
    protected function defineSettings()
    {
        return array(
           'name' => array(AttributeType::String),
           'colspan' => array(AttributeType::Number, 'default' => 2),
           'options' => array(AttributeType::Mixed),
        );
    }

    public function isSelectable()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        if($settings->enableCustomReportWidget)
        {
            return true;
        }

        return false;
    }

    public function getName()
    {
        $settings = $this->getSettings();

        if(!empty($settings->name))
        {
            return $settings->name;
        }
        else
        {
            return Craft::t('Analytics Custom Report');
        }
    }


    public function getSettingsHtml()
    {
        return craft()->templates->render('analytics/widgets/customReport/settings', array(
           'settings' => $this->getSettings()
        ));
    }

    public function getBodyHtml()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $this->getSettings();

        $variables = array(
            'pluginSettings' => $plugin->getSettings(),
            'settings' => $settings,
            'widget' => $this,
            'colspan' => $this->getColspan()
        );

        craft()->templates->includeJs('new AnalyticsCustomReport("analytics-widget-'.$this->model->id.'");');

        $html = craft()->templates->render('analytics/widgets/customReport', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
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
}
