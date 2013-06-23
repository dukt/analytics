<?php

namespace Craft;

class Analytics_SettingsRecord extends BaseRecord
{
    /**
     * Get Table Name
     */
    public function getTableName()
    {
        return 'analytics_settings';
    }

    // --------------------------------------------------------------------

    /**
     * Define Attributes
     */
    public function defineAttributes()
    {
        return array(
            'options' => array(AttributeType::Mixed)
        );
    }
}