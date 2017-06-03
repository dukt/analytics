<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace dukt\analytics\records;

use craft\db\ActiveRecord;

class View extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the associated database table.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'analytics_views';
    }

    // Protected Methods
    // =========================================================================

    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    public function defineAttributes()
    {
        return array(
            'name' => array(AttributeType::String, 'required' => true),
            'reportingViewId' => array(AttributeType::String, 'required' => true)
        );
    }
}
