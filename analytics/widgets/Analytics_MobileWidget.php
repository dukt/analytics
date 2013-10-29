<?php

namespace Craft;

class Analytics_MobileWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Mobile');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/mobile', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}