<?php
namespace dukt\analytics\models;

use craft\base\Model;

/**
 * Class Settings
 *
 * @package dukt\analytics\models
 */
class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string|null Google Analytics Account ID
     */
    public $accountId;

    /**
     * @var string|null Google Analytics Account Name
     */
    public $accountName;

    /**
     * @var string|null Web Property ID
     */
    public $webPropertyId;

    /**
     * @var string|null Web Property Name
     */
    public $webPropertyName;

    /**
     * @var string|null Internal Web Property ID
     */
    public $internalWebPropertyId;

    /**
     * @var string|null Profile ID
     */
    public $profileId;

    /**
     * @var string|null Profile Name
     */
    public $profileName;

    /**
     * @var string|null Profile Currency
     */
    public $profileCurrency;

    /**
     * @var int Realtime Refresh Interval (in seconds)
     */
    public $realtimeRefreshInterval = 60;

    /**
     * @var bool Force connect
     */
    public $forceConnect = false;

    /**
     * @var bool Enable Realtime
     */
    public $enableRealtime = false;

    /**
     * @var string
     */
    public $token;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['realtimeRefreshInterval'], 'number', 'integerOnly' => true],
        ];
    }
}
