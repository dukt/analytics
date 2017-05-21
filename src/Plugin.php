<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use craft\web\UrlManager;
use dukt\analytics\base\PluginTrait;
use dukt\analytics\fields\Report as ReportField;
use dukt\analytics\models\Settings;
use dukt\analytics\web\twig\variables\AnalyticsVariable;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\widgets\Realtime;
use dukt\analytics\widgets\Report;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package dukt\analytics
 */
class Plugin extends \craft\base\Plugin
{
    // Traits
    // =========================================================================

    use PluginTrait;

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * @var \dukt\analytics\Plugin The plugin instance.
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'analytics' => \dukt\analytics\services\Analytics::class,
            'analyticsReportingApi' => \dukt\analytics\services\AnalyticsReportingApi::class,
            'api' => \dukt\analytics\services\Api::class,
            'cache' => \dukt\analytics\services\Cache::class,
            'metadata' => \dukt\analytics\services\Metadata::class,
            'oauth' => \dukt\analytics\services\Oauth::class,
            'reports' => \dukt\analytics\services\Reports::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Realtime::class;
            $event->types[] = Report::class;
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = ReportField::class;
        });

        if (Craft::$app->getRequest()->getIsCpRequest())
        {
            Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);
        }
    }

    /**
     * Register CP rules.
     *
     * @param RegisterUrlRulesEvent $event
     */
    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'analytics/settings' => 'analytics/settings/index',
            'analytics/settings/oauth' => 'analytics/settings/oauth',
            'analytics/utils' => 'analytics/utils/metadata',
            'analytics/utils/metadata' => 'analytics/utils/metadata',
            'analytics/tests/data-types' => 'analytics/tests/data-types',
            'analytics/tests' => 'analytics/tests/columns',
            'analytics/tests/columns' => 'analytics/tests/columns',
            'analytics/tests/column-groups' => 'analytics/tests/column-groups',
            'analytics/tests/formatting' => 'analytics/tests/formatting',
            'analytics/tests/report-widgets' => 'analytics/tests/report-widgets',
            'analytics/api4' => 'analytics/api4',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

    /**
     * @inheritdoc
     */
    public function defineTemplateComponent()
    {
        return AnalyticsVariable::class;
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return Settings
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    public function getSettingsResponse()
    {
        $url = UrlHelper::cpUrl('analytics/settings');

        Craft::$app->controller->redirect($url);

        return '';
    }
}
