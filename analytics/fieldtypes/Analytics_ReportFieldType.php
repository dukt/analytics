<?php

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

        if($this->element->uri)
        {
            craft()->templates->includeJs('new AnalyticsField("'.$namespacedId.'-field");');

            // render HTML
            return craft()->templates->render('analytics/field/field', array(
                'isNew' => false,
                'hasUrl' => true,
                'id'    => $id,
                'name'  => $name,
                'value' => $value,
                'model' => $this->model,
                'element' => $this->element,
            ));
        }
        elseif(!$this->element->id)
        {
            return craft()->templates->render('analytics/field/field', array(
                'hasUrl' => false,
                'isNew' => true,
            ));
        }
        else
        {
            return craft()->templates->render('analytics/field/field', array(
                'hasUrl' => false,
                'isNew' => false,
            ));
        }
    }
}
