<?php

namespace Craft;

class Analytics_AcquisitionWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Acquisition');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/acquisition', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}