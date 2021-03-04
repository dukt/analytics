<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2021, Dukt
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
    public $gaViewCurrency;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['name', 'gaAccountId', 'gaAccountName', 'gaPropertyId', 'gaPropertyName', 'gaViewId', 'gaViewName', 'gaViewCurrency'], 'required'],
            [['id'], 'number', 'integerOnly' => true]
        ];

        return $rules;
    }
}
