<?php
namespace Craft;

class Analytics_ActivityWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics Activity');
    }

    public function getBodyHtml()
    {
    	$variables = array();

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_widgets/activity', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}