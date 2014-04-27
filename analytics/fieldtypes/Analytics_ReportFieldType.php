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

        // let's roll

        // profile
        $profile = craft()->analytics->getProfile();

        // date
        $start = date('Y-m-d', strtotime('-1 month'));
        $end = date('Y-m-d', strtotime('-1 day'));

        // metrics
        $metrics = 'ga:pageviews, ga:bounces';

        // options & dimensions
        $options = array(
            'dimensions' => 'ga:date',
            'filters' => "ga:pagePath==/".$this->element->uri
        );

        // api request
        $result = craft()->analytics->api()->data_ga->get(
                    'ga:'.$profile['id'],
                    $start,
                    $end,
                    $metrics,
                    $options
                );

        craft()->templates->includeJs('new AnalyticsField("'.$namespacedId.'-field");');

        // render HTML
        return craft()->templates->render('analytics/field/field', array(
            'id'    => $id,
            'name'  => $name,
            'value' => $value,
            'model' => $this->model,
            'element' => $this->element,
            'start' => $start,
            'end' => $end,
            'result' => $result
        ));
    }
}
