<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;

class Column extends Model
{
    public $id;
    public $type;
    public $dataType;
    public $group;
    public $status;
    public $uiName;
    public $description;
    public $allowInSegments;
    public $addedInApiVersion;

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritDoc BaseModel::defineAttributes()
	 *
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'id' => AttributeType::String,
			'type' => AttributeType::String,
			'dataType' => AttributeType::String,
			'group' => AttributeType::String,
			'status' => AttributeType::String,
			'uiName' => AttributeType::String,
			'description' => AttributeType::String,
			'allowInSegments' => AttributeType::Bool,
			'addedInApiVersion' => AttributeType::Number,
		);
	}
}
