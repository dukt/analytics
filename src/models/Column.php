<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;

/**
 * Class Column
 *
 * @package dukt\analytics\models
 */
class Column extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string Column ID
     */
    public $id;

    /**
     * @var string Column Type
     */
    public $type;

    /**
     * @var string Data Type
     */
    public $dataType;

    /**
     * @var string Group
     */
    public $group;

    /**
     * @var string Status
     */
    public $status;

    /**
     * @var string UI Name
     */
    public $uiName;

    /**
     * @var string Description
     */
    public $description;

    /**
     * @var bool Allow in segments
     */
    public $allowInSegments;

    /**
     * @var int Added in API version
     */
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
