<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace dukt\analytics\records;

use craft\db\ActiveRecord;

/**
 * View record.
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string $gaAccountId
 * @property string $gaAccountName
 * @property string $gaPropertyId
 * @property string $gaPropertyName
 * @property string $gaViewId
 * @property string $gaViewName
 * @property string $gaCurrency
 */
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
        return '{{%analytics_views}}';
    }
}
