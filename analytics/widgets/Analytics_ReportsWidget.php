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

class Analytics_ReportsWidget extends BaseWidget
{
	private $types = array(
        'acquisition' => "Acquisition",
        'counts'      => "Counts",
        'custom'      => "Custom",
        'geo'         => "Geo",
        'mobile'      => "Mobile",
        'pages'       => "Pages",
        'realtime'    => "Real-Time",
        'technology'  => "Technology",
        'visits'      => "Visits"
	);

    protected function defineSettings()
    {
        return array(
           'name' => array(AttributeType::String),
           'type' => array(AttributeType::String),
           'colspan' => array(AttributeType::Number, 'default' => 2),
           'options' => array(AttributeType::Mixed),
        );
    }

    public function getName()
    {
        $settings = $this->getSettings();

        if(!empty($settings->name))
        {
            return $settings->name;
        }
        else
        {
            if($settings->type == 'counts' && craft()->request->getSegment(2) != 'settings')
            {
                return null;
            }
            else
            {
                $type = $this->getType($settings->type);

                if($type) {
                    return Craft::t('Analytics '.$type);
                }
            }
        }

        return Craft::t('Analytics Report');
    }


    public function getSettingsHtml()
    {
        $types = $this->getTypes();

        foreach($types as $k => $type)
        {
            $types[$k] = Craft::t($type);
        }

        if(!empty($types['realtime'])) {
            $types['realtime'] .= ' (beta)';
        }

        return craft()->templates->render('analytics/widgets/report/settings', array(
           'settings' => $this->getSettings(),
           'types' => $types
        ));
    }

    public function getBodyHtml()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $this->getSettings();

        $variables = array(
            'pluginSettings' => $plugin->getSettings(),
            'settings' => $this->getSettings(),
            'colspan' => $this->getColspan(),
            'type' => $settings['type'],
            'widget' => $this,
        );

        $settings = $this->getSettings();

        switch($settings->type)
        {
            case 'counts':
            craft()->templates->includeJs('new AnalyticsCountReport("analytics-widget-'.$this->model->id.'");');
            break;

            case 'realtime':
            craft()->templates->includeJs('new AnalyticsRealtimeReport("analytics-widget-'.$this->model->id.'");');
            break;

            case 'custom':
            craft()->templates->includeJs('new AnalyticsCustomReport("analytics-widget-'.$this->model->id.'");');
            return craft()->templates->render('analytics/widgets/customReport', $variables);
            break;

            default:
            craft()->templates->includeJs('new AnalyticsReport("analytics-widget-'.$this->model->id.'");');
        }

        if(craft()->templates->doesTemplateExist('analytics/widgets/report/'.$settings->type))
        {
            return craft()->templates->render('analytics/widgets/report/'.$settings->type, $variables);
        }
        else
        {
            return '<span class="error">'.Craft::t("This report doesn’t exists").'</span>';
        }

    }


    public function getType($k)
    {
        if(!empty($this->types[$k])) {
            return $this->types[$k];
        }
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getColspan()
    {
        $settings = $this->getSettings();

        if(isset($settings->colspan))
        {
            if($settings->colspan > 0)
            {
                return $settings->colspan;
            }
        }

        return 1;
    }
}