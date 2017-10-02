<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace Craft;

class Analytics_ColumnModel extends BaseModel
{
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
