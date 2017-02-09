<?php
namespace dukt\analytics\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $accountId;
    public $accountName;
    public $webPropertyId;
    public $webPropertyName;
    public $internalWebPropertyId;
    public $profileId;
    public $profileName;
    public $profileCurrency;
    public $realtimeRefreshInterval;
    public $forceConnect;
    public $enableRealtime;
    public $tokenId;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['tokenId'], 'number', 'integerOnly' => true],
        ];
    }
}
