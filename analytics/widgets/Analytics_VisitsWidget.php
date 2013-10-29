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

        $html = craft()->templates->render('analytics/_widgets/visits', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}