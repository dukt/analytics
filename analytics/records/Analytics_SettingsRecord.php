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