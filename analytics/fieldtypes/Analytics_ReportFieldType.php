<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

class Analytics_ReportFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Analytics Report');
    }

    /**
     * Save it
     */
    public function defineContentAttribute()
    {
        return AttributeType::String;
    }

    /**
     * Show field
     */
    public function getInputHtml($name, $value)
    {
        // Reformat the input name into something that looks more like an ID
        $id = craft()->templates->formatInputId($name);

        // Figure out what that ID is going to look like once it has been namespaced
        $namespacedId = craft()->templates->namespaceInputId($id);

        $variables = array();

        if($this->element->uri)
        {
            $uri = craft()->analytics->getElementUrlPath($this->element->id, $this->element->locale);

            craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
            craft()->templates->includeJs('new AnalyticsField("'.$namespacedId.'-field");');

            $variables = array(
                'isNew'   => false,
                'hasUrl'  => true,
                'id'      => $id,
                'uri'     => $uri,
                'name'    => $name,
                'value'   => $value,
                'model'   => $this->model,
                'element' => $this->element
            );
        }
        elseif(!$this->element->id)
        {
            $variables = array(
                'hasUrl' => false,
                'isNew' => true,
            );
        }
        else
        {
            $variables = array(
                'hasUrl' => false,
                'isNew' => false,
            );
        }

        return craft()->templates->render('analytics/field/field', $variables);
    }
}
