<?php

namespace Craft;

class Analytics_RealtimeWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Real Time');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/realtime', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}