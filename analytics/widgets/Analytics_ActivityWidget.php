<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/craft/analytics/docs#license
 * @link      http://dukt.net/craft/analytics/
 */

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