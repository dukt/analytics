<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace dukt\analytics\records;

use craft\db\ActiveRecord;

/**
 * SiteView record.
 *
 * @property int $id
 * @property int $siteId
 * @property int $sourceId
 */
class SiteSource extends ActiveRecord
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
        return '{{%analytics_site_sources}}';
    }
}
