<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Fields;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use dukt\analytics\base\PluginTrait;
use dukt\analytics\fields\Report as ReportField;
use dukt\analytics\models\Settings;
use dukt\analytics\web\twig\variables\AnalyticsVariable;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use dukt\analytics\widgets\Ecommerce;
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
    public bool $hasCpSettings = true;

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
            'apis' => \dukt\analytics\services\Apis::class,
            'cache' => \dukt\analytics\services\Cache::class,
            'geo' => \dukt\analytics\services\Geo::class,
            'metadata' => \dukt\analytics\services\Metadata::class,
            'oauth' => \dukt\analytics\services\Oauth::class,
            'reports' => \dukt\analytics\services\Reports::class,
            'views' => \dukt\analytics\services\Views::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event): void {
            $rules = [
                'analytics/settings' => 'analytics/settings/index',
                'analytics/settings/oauth' => 'analytics/settings/oauth',
                'analytics/settings/views' => 'analytics/settings/views',
                'analytics/settings/views/new' => 'analytics/settings/edit-view',
                'analytics/settings/views/<viewId:\d+>' => 'analytics/settings/edit-view',
                'analytics/settings/sites' => 'analytics/settings/sites',
                'analytics/settings/sites/<siteId:\d+>' => 'analytics/settings/edit-site',
                'analytics/utils' => 'analytics/utils/metadata',
                'analytics/utils/metadata' => 'analytics/utils/metadata',
                'analytics/tests/data-types' => 'analytics/tests/data-types',
                'analytics/tests' => 'analytics/tests/columns',
                'analytics/tests/columns' => 'analytics/tests/columns',
                'analytics/tests/column-groups' => 'analytics/tests/column-groups',
                'analytics/tests/formatting' => 'analytics/tests/formatting',
                'analytics/tests/report-widgets' => 'analytics/tests/report-widgets',
                'analytics/tests/template-variables' => 'analytics/tests/template-variables',
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
            $variable->set('analytics', AnalyticsVariable::class);
        });

        if ($this->isInstalled && !Craft::$app->getRequest()->getIsConsoleRequest() && Craft::$app->getRequest()->getIsCpRequest()) {
            Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);
        }

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
