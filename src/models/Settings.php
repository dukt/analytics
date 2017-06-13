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
     * @var string The amount of time cache should last.
     *
     * @see http://www.php.net/manual/en/dateinterval.construct.php
     */
    public $cacheDuration = 'PT10M';

    /**
     * @var bool Whether request to APIs should be cached or not
     */
    public $enableCache = true;

    /**
     * @var bool Whether Analytics widgets are enabled or disabled
     */
    public $enableWidgets = true;

    /**
     * @var bool Whether Analytics fieldtype is enabled or not
     */
    public $enableFieldtype = true;

    /**
     * @var string OAuth token
     */
    public $token;

    /**
     * @var array OAuth provider options
     */
    public $oauthProviderOptions = [];

    /**
     * @var string OAuth client ID
     */
    public $oauthClientId;

    /**
     * @var string OAuth client ID
     */
    public $oauthClientSecret;

    /**
     * Google API key
     */
    public $apiKey;

    /**
     * @var bool Demo mode
     */
    public $demoMode = false;

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
