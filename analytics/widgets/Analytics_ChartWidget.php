<?php

namespace Craft;

class Analytics_ChartWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics Chart');
    }

    protected function defineSettings()
    {
        return array(
            'chartTemplate' => AttributeType::String,
            'title' => AttributeType::String,
            'chartType' => AttributeType::String,
            'periodQuantity' => AttributeType::Number,
            'periodUnit' => AttributeType::String,
        );
    }

    public function getCharts()
    {
        $chartTemplates = array(
            'acquisition/keywords',
            'acquisition/socialNetwork',
            'acquisition/trafficSource',
            'audience/browsers',
            'audience/country',
            'audience/isMobile',
            'audience/language',
            'audience/mobileDevice',
            'audience/visits',
            'behavior/pages',
        );

        $charts = array();


        foreach($chartTemplates as $chartTemplate) {
            $json = craft()->templates->render('analytics/charts/'.$chartTemplate, array(
                    'settings' => $this->getSettings()
                ));

            $object = json_decode(trim($json));


            $charts[$chartTemplate] = $object;
            $charts[$chartTemplate]->template = $chartTemplate;
        }

        return $charts;
    }

    public function getTitle()
    {
        $charts = $this->getCharts();
        $settings = $this->getSettings();

        if(!empty($settings->title)) {
            return $settings->title;
        }

        if(isset($charts[$settings->chartTemplate])) {
            return $charts[$settings->chartTemplate]->title;
        }

        return Craft::t('Analytics Chart');
    }

    public function getBodyHtml()
    {
        $charts = $this->getCharts();
        $settings = $this->getSettings();
        $chart = false;

        if(isset($charts[$settings->chartTemplate])) {
            $chart = $charts[$settings->chartTemplate];
        }

        $variables = array(
                'settings' => $chart,
            );

        $originalTemplatesPath = craft()->path->getTemplatesPath();

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_widgets/chart', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        $return = new \Twig_Markup($html, $charset);

        craft()->path->setTemplatesPath($originalTemplatesPath);

        return $return;
    }

    public function getSettingsHtml()
    {

        $charts = $this->getCharts();
        $chartTemplatesOpts = array();

        foreach($charts as $chart) {
            $chartTemplatesOpts[$chart->template] = $chart->title;
        }

        return craft()->templates->render('analytics/_widgets/chartSettings', array(
            'chartTemplatesOpts' => $chartTemplatesOpts,
            'settings' => $this->getSettings()
        ));
    }
}