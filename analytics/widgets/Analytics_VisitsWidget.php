<?php

namespace Craft;

class Analytics_VisitsWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Visits');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $originalTemplatesPath = craft()->path->getTemplatesPath();

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_widgets/visits', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        $return = new \Twig_Markup($html, $charset);

        craft()->path->setTemplatesPath($originalTemplatesPath);

        return $return;
    }
}