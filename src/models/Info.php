<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\models;

use craft\base\Model;
use craft\helpers\Json;

class Info extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var bool Force connect
     */
    public $forceConnect = false;

    /**
     * @var string|null Token
     */
    public $token;

    /**
     * @var \DateTime|null Date updated
     */
    public $dateUpdated;

    /**
     * @var \DateTime|null Date created
     */
    public $dateCreated;

    /**
     * @var string|null Uid
     */
    public $uid;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Make sure $forceConnect is going to be a boolean
        if (is_string($this->forceConnect)) {
            $this->forceConnect = (bool)$this->forceConnect;
        }

        if (is_string($this->token)) {
            $this->token = Json::decode($this->token);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'edition'], 'number', 'integerOnly' => true],
        ];
    }
}
