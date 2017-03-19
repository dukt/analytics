<?php
namespace dukt\analytics\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public $accountId;
    public $accountName;
    public $webPropertyId;
    public $webPropertyName;
    public $internalWebPropertyId;
    public $profileId;
    public $profileName;
    public $profileCurrency;
    public $realtimeRefreshInterval = 60;
    public $forceConnect = false;
    public $enableRealtime;
    public $token;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['realtimeRefreshInterval'], 'number', 'integerOnly' => true],
        ];
    }
}
