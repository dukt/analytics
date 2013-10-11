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

class Analytics_SourcesWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics Sources');
    }

    public function getBodyHtml()
    {

        $variables = array();

        $originalTemplatesPath = craft()->path->getTemplatesPath();

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_widgets/sources', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        $return = new \Twig_Markup($html, $charset);

        craft()->path->setTemplatesPath($originalTemplatesPath);

        return $return;
    }
}