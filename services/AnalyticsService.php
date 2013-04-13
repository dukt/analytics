<?php

/**
 * Craft Analytics
 *
 * @package     Craft Analytics
 * @version     Version 1.0
 * @author      Benjamin David
 * @copyright   Copyright (c) 2013 - DUKT
 * @link        http://dukt.net/add-ons/craft/analytics/
 *
 */

namespace Craft;

class AnalyticsService extends BaseApplicationComponent
{
    public function code($id)
    {
        $variables = array('id' => $id);

        $html = craft()->templates->render('analytics/_code', $variables);

        $charset = craft()->templates->getTwig()->getCharset();
        
        return new \Twig_Markup($html, $charset);
    }
}

