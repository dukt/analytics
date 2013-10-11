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
            'chartTemplate' => AttributeType::String
        );
    }

    public function getBodyHtml()
    {
        die('test');
    	$settings = $this->getSettings();

        $variables = array(
                'settings' => $settings,
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
        return craft()->templates->render('analytics/_widgets/chartSettings', array(
            'settings' => $this->getSettings()
        ));
    }
}