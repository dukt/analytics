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
     * @var string The amount of time cache should last. The value should be set as a [PHP date interval](http://www.php.net/manual/en/dateinterval.construct.php).
     */
    public $cacheDuration = 'PT10M';

    /**
     * @var bool Whether requests to APIs should be cached or not.
     */
    public $enableCache = true;

    /**
     * @var bool Whether the Report field type is enabled or not.
     */
    public $enableFieldtype = true;

    /**
     * @var bool Whether the Realtime widget is enabled or not.
     */
    public $enableRealtime = false;

    /**
     * @var bool Whether Analytics widgets are enabled or disabled.
     */
    public $enableWidgets = true;

    /**
     * @var bool Whether to enable Analytics in the main sidebar navigation.
     */
    public bool $hasCpSection = true;

    /**
     * @var string|null Google Maps API key. Used by the Geo chart.
     */
    public $mapsApiKey;

    /**
     * @var string|null The Google API application’s OAuth client ID.
     */
    public $oauthClientId;

    /**
     * @var string|null The Google API application’s OAuth client Secret.
     */
    public $oauthClientSecret;

    /**
     * @var array OAuth provider options.
     */
    public $oauthProviderOptions = [];

    /**
     * @var int Interval at which the realtime widget should refresh its data (in seconds).
     */
    public $realtimeRefreshInterval = 60;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['realtimeRefreshInterval'], 'number', 'integerOnly' => true],
            [['realtimeRefreshInterval'], 'required'],
        ];
    }
}
