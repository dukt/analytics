<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;

/**
 * Class View
 *
 * @package dukt\analytics\models
 */
class View extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int ID
     */
    public $id;

    /**
     * @var string Name
     */
    public $name;

    /**
     * @var string Google Analytics Account ID
     */
    public $gaAccountId;
    /**
     * @var string Google Analytics Account Name
     */
    public $gaAccountName;

    /**
     * @var string Google Analytics Property ID
     */
    public $gaPropertyId;

    /**
     * @var string Google Analytics Property Name
     */
    public $gaPropertyName;

    /**
     * @var string Google Analytics View ID
     */
    public $gaViewId;

    /**
     * @var string Google Analytics View Name
     */
    public $gaViewName;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name'], 'required'],
            [['id'], 'number', 'integerOnly' => true]
        ];

        return $rules;
    }
}
