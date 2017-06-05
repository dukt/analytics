<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace dukt\analytics\records;

use craft\db\ActiveRecord;

class SiteView extends ActiveRecord
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
        return '{{%analytics_site_views}}';
    }
}
