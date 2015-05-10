<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportFieldType extends BaseFieldType
{
    // Public Methods
    // =========================================================================

    /**
     * Get Name
     */
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
        $disableAnalytics = false;

        if(craft()->config->get('disableAnalytics') === null)
        {
            if(craft()->config->get('disableAnalytics', 'analytics') === true)
            {
                $disableAnalytics = true;
            }
        }
        else
        {
            if(craft()->config->get('disableAnalytics') === true)
            {
                $disableAnalytics = true;
            }
        }

        if($disableAnalytics)
        {
            return craft()->templates->render('analytics/widgets/explorer/disabled', array());
        }

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
