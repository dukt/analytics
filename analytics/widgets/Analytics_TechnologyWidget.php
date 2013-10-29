<?php

namespace Craft;

class Analytics_TechnologyWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Technology');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/technology', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}