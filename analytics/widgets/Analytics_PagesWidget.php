<?php

namespace Craft;

class Analytics_PagesWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics : Pages');
    }

    public function getBodyHtml()
    {
        $variables = array();

        $html = craft()->templates->render('analytics/_widgets/pages', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }
}