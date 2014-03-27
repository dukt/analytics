<?php

namespace Craft;

class Analytics_ReportsWidget extends BaseWidget
{
	private $types = array(
        'acquisition' => "Acquisition",
        'conversions' => "Conversions",
        'counts'      => "Counts",
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

        return Craft::t('Analytics');
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

        return craft()->templates->render('analytics/_widgets/settings', array(
           'settings' => $this->getSettings(),
           'types' => $types
        ));
    }

    public function getBodyHtml()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $this->getSettings();

        $variables = array(
            'settings' => $plugin->getSettings(),
            'colspan' => $this->getColspan(),
            'type' => $settings['type']
        );

        $settings = $this->getSettings();

        $html = craft()->templates->render('analytics/_widgets/'.$settings->type, $variables);

        $charset = craft()->templates->getTwig()->getCharset();

        return new \Twig_Markup($html, $charset);
    }


    public function getType($k)
    {
        if(!empty($k)) {
            return $this->types[$k];
        }
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getColspan()
    {
        if(craft()->version > 1.3)
        {
            $settings = $this->getSettings();

            if(isset($settings->colspan))
            {
                if($settings->colspan > 0)
                {
                    return $settings->colspan;
                }
            }
        }

        return 1;
    }
}