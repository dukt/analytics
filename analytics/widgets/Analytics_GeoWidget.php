<?php

namespace Craft;

class Analytics_GeoWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Geo');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/geo', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}