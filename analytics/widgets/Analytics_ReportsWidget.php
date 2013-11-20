<?php

namespace Craft;

class Analytics_ReportsWidget extends BaseWidget
{
	private $types = array(
		'acquisition' => "Acquisition",
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
           'type' => array(AttributeType::String)
        );
    }

    public function getName()
    {
        $settings = $this->getSettings();

        $type = $this->getType($settings->type);

        if($type) {
            return Craft::t('Analytics')." ".$type;
        }

        return Craft::t('Analytics');
    }


    public function getSettingsHtml()
    {
        $types = $this->getTypes();

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

        $variables = array(
            'settings' => $plugin->getSettings(),
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
}