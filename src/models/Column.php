<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;

class Column extends Model
{
    // Properties
    // =========================================================================

    public $id;
    public $type;
    public $dataType;
    public $group;
    public $status;
    public $uiName;
    public $description;
    public $allowInSegments;
    public $addedInApiVersion;
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['addedInApiVersion'], 'number', 'integerOnly' => true],
        ];
    }
}
