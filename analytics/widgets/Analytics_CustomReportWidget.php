<?php

namespace Craft;

class Analytics_CustomReportWidget extends BaseWidget
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
           'colspan' => array(AttributeType::Number, 'default' => 2),
           'options' => array(AttributeType::Mixed),
        );
    }

    public function getName()
    {
        return Craft::t('Analytics Custom Report');
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

        $dimensionsOpts = $this->getDimensionsOpts();
        $metricsOpts = $this->getMetricsOpts();

        return craft()->templates->render('analytics/_widgets/customReportSettings', array(
           'settings' => $this->getSettings(),
           'types' => $types,
           'dimensionsOpts' => $dimensionsOpts,
           'metricsOpts' => $metricsOpts
        ));
    }

    public function getBodyHtml()
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $this->getSettings();

        $variables = array(
            'pluginSettings' => $plugin->getSettings(),
            'settings' => $settings,
            'widget' => $this,
            'colspan' => $this->getColspan()
        );

        $html = craft()->templates->render('analytics/_widgets/customReport', $variables);

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

    private function getMetricsOpts()
    {
        return array(

            // User

            array('optgroup' => 'User'),
            array(
                'label' => 'ga:users',
                'value' => 'ga:users'
            ),
            array(
                'label' => 'ga:newUsers',
                'value' => 'ga:newUsers'
            ),

            // Page Tracking
            array('optgroup' => 'Page Tracking'),
            array(
                'label' => 'ga:exits',
                'value' => 'ga:exits'
            ),

            array(
                'label' => 'ga:pageviews',
                'value' => 'ga:pageviews'
            ),
        );
    }

    private function getDimensionsOpts()
    {
        return array(

            // User

            array('optgroup' => "User"),
            array(
                'label' => "ga:userType",
                'value' => "ga:userType"
            ),
            array(
                'label' => 'ga:sessionCount',
                'value' => 'ga:sessionCount'
            ),
            array(
                'label' => 'ga:daysSinceLastSession',
                'value' => 'ga:daysSinceLastSession'
            ),
            array(
                'label' => 'ga:userDefinedValue',
                'value' => 'ga:userDefinedValue'
            ),


            // Session

            array('optgroup' => 'Session'),
            array(
                'label' => 'ga:sessionDurationBucket',
                'value' => 'ga:sessionDurationBucket'
            ),

            // Time

            array('optgroup' => 'Time'),
            array(
                'label' => 'ga:date',
                'value' => 'ga:date'
            ),

            // Page Tracking

            array('optgroup' => 'Page Tracking'),
            array(
                'label' => 'ga:pagePath',
                'value' => 'ga:pagePath'
            ),
            array(
                'label' => 'ga:exitPagePath',
                'value' => 'ga:exitPagePath'
            )
        );
    }
}
