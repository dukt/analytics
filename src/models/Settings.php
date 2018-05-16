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
     * @var string The amount of time cache should last
     *
     * @see http://www.php.net/manual/en/dateinterval.construct.php
     */
    public $cacheDuration = 'PT10M';

    /**
     * @var bool|string|null Demo mode
     */
    public $demoMode = false;

    /**
     * @var bool Whether request to APIs should be cached or not
     */
    public $enableCache = true;

    /**
     * @var bool Whether Analytics fieldtype is enabled or not
     */
    public $enableFieldtype = true;

    /**
     * @var bool Enable Realtime
     */
    public $enableRealtime = false;

    /**
     * @var bool Whether Analytics widgets are enabled or disabled
     */
    public $enableWidgets = true;

    /**
     * @var bool Force connect
     */
    public $forceConnect = false;

    /**
     * @var string|null Google Maps API key
     */
    public $mapsApiKey;

    /**
     * @var string|null OAuth client ID
     */
    public $oauthClientId;

    /**
     * @var string|null OAuth client ID
     */
    public $oauthClientSecret;

    /**
     * @var array OAuth provider options
     */
    public $oauthProviderOptions = [];

    /**
     * @var int Realtime Refresh Interval (in seconds)
     */
    public $realtimeRefreshInterval = 60;

    /**
     * @var string|null OAuth token
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
            [['realtimeRefreshInterval'], 'required'],
        ];
    }
}
