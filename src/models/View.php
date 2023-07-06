<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
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
     * @var string Type (UA or GA4)
     */
    public $type;
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

    /**
     * @var string Google Analytics View Currency
     */
    public $gaCurrency;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = [
            [['type', 'name', 'gaAccountId', 'gaAccountName', 'gaPropertyId', 'gaPropertyName'], 'required'],
            [['id'], 'number', 'integerOnly' => true]
        ];

        return $rules;
    }
}
