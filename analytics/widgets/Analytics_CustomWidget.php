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

class Analytics_CustomWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Analytics Custom');
    }

    protected function defineSettings()
    {
        return array(
            'title' => AttributeType::String,
            'chartType' => AttributeType::String,
            'dimensions' => AttributeType::String,
            'metric' => AttributeType::String,
            'periodQuantity' => AttributeType::Number,
            'periodUnit' => AttributeType::String,
        );
    }

    public function getTitle()
    {
        $settings = $this->getSettings();

        return $settings->title;
    }



    public function getBodyHtml()
    {

        $variables = array(
                'settings' => $this->getSettings(),
            );

        $originalTemplatesPath = craft()->path->getTemplatesPath();

        $templatePath = craft()->path->getPluginsPath().'analytics/templates/';

        craft()->path->setTemplatesPath($templatePath);

        $html = craft()->templates->render('_widgets/custom', $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        $return = new \Twig_Markup($html, $charset);

        craft()->path->setTemplatesPath($originalTemplatesPath);

        return $return;
    }

    /**
     * Returns the widget's body HTML.
     *
     * @return string
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('analytics/_widgets/customSettings', array(
            'settings' => $this->getSettings()
        ));
    }
}