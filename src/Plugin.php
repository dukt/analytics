<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\App;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use dukt\analytics\base\PluginTrait;
use dukt\analytics\fields\Report as ReportField;
use dukt\analytics\models\Settings;
use dukt\analytics\web\assets\analyticsvue\AnalyticsVueAsset;
use dukt\analytics\web\twig\variables\AnalyticsVariable;
use dukt\analytics\widgets\Ecommerce;
use dukt\analytics\widgets\Realtime;
use dukt\analytics\widgets\Report;
use yii\base\Event;
use nystudio107\pluginvite\services\VitePluginService;

/**
 * Class Plugin
 *
 * @method Settings getSettings()
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
    public bool $hasCpSettings = true;

    /**
     * @var \dukt\analytics\Plugin The plugin instance.
     */
    public static $plugin;

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function config(): array
    {
        return [
            'components' => [
                'analytics' => \dukt\analytics\services\Analytics::class,
                'apis' => \dukt\analytics\services\Apis::class,
                'cache' => \dukt\analytics\services\Cache::class,
                'geo' => \dukt\analytics\services\Geo::class,
                'metadata' => \dukt\analytics\services\Metadata::class,
                'oauth' => \dukt\analytics\services\Oauth::class,
                'reports' => \dukt\analytics\services\Reports::class,
                'views' => \dukt\analytics\services\Views::class,
                'vite' => [
                    'class' => VitePluginService::class,
                    'assetClass' => AnalyticsVueAsset::class,
                    'useDevServer' => true,
                    'devServerPublic' => 'http://localhost:'.App::env('ANALYTICS_VITE_DEV_PORT'),
                    'serverPublic' => 'https://playground.ddev.site',
                    'errorEntry' => 'src/js/Retour.js',
                    'devServerInternal' => 'http://host.docker.internal:'.App::env('ANALYTICS_VITE_DEV_PORT'),
                    'checkDevServer' => true,
                ],
            ]
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;


        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event): void {
            $rules = [
                'analytics/settings' => 'analytics/settings/index',
                'analytics/settings/oauth' => 'analytics/settings/oauth',
                'analytics/settings/views' => 'analytics/settings/views',
                'analytics/settings/views/new' => 'analytics/settings/edit-view',
                'analytics/settings/views/<viewId:\d+>' => 'analytics/settings/edit-view',
                'analytics/settings/sites' => 'analytics/settings/sites',
                'analytics/settings/sites/<siteId:\d+>' => 'analytics/settings/edit-site',
                'analytics/utils' => 'analytics/utils/index',
                'analytics/tests' => 'analytics/tests/overview',
                'analytics/tests/overview' => 'analytics/tests/overview',
                'analytics/tests/formatting' => 'analytics/tests/formatting',
                'analytics/tests/template-variables' => 'analytics/tests/template-variables',
                'analytics/tests/ga4' => 'analytics/tests/ga4',
                'analytics/tests/ga4-metadata' => 'analytics/tests/ga4-metadata',
                'analytics/tests/vue' => 'analytics/tests/vue',
                'analytics/tests/vue-reports' => 'analytics/tests/vue-reports',
                'analytics/api4' => 'analytics/api4',
            ];

            $event->rules = array_merge($event->rules, $rules);
        });

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event): void {
            $event->types[] = Ecommerce::class;
            $event->types[] = Realtime::class;
            $event->types[] = Report::class;
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event): void {
            $event->types[] = ReportField::class;
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event): void {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('analytics', [
                'class' => AnalyticsVariable::class,
                'viteService' => $this->vite,
            ]);
        });

        Craft::setAlias('@analyticsLib', __DIR__ . '/../lib');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        $url = UrlHelper::cpUrl('analytics/settings');

        Craft::$app->controller->redirect($url);

        return '';
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }
}
