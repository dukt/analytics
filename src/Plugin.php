<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use yii\base\Event;
use dukt\analytics\models\Settings;
use dukt\analytics\web\assets\analytics\AnalyticsAsset;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use dukt\analytics\widgets\RealtimeWidget;
use dukt\analytics\widgets\ReportWidget;

class Plugin extends \craft\base\Plugin
{
    public $hasSettings = true;

	// Public Methods
	// =========================================================================

	public function init()
	{
		parent::init();

        $this->setComponents([
            'analytics' => \dukt\analytics\services\Analytics::class,
            'analytics_api' => \dukt\analytics\services\Api::class,
            'analytics_cache' => \dukt\analytics\services\Cache::class,
            'analytics_metadata' => \dukt\analytics\services\Metadata::class,
            'analytics_oauth' => \dukt\analytics\services\Oauth::class,
            'analytics_reports' => \dukt\analytics\services\Reports::class,
        ]);

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, [$this, 'registerCpUrlRules']);


        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = RealtimeWidget::class;
            $event->types[] = ReportWidget::class;
        });

		// Global JS variables

		if (Craft::$app->request->getIsCpRequest())
		{
            Craft::$app->getView()->registerAssetBundle(AnalyticsAsset::class);

/*			Craft::$app->getView()->registerJsFile('analytics/js/Analytics.js');

			$continents = $this->analytics_metadata->getContinents();
			$subContinents = $this->analytics_metadata->getSubContinents();
			$formats = ChartHelper::formats();
			$currency = $this->analytics->getD3LocaleDefinitionCurrency();

			Craft::$app->getView()->registerJs('Analytics.continents = '.json_encode($continents));
			Craft::$app->getView()->registerJs('Analytics.subContinents = '.json_encode($subContinents));
			Craft::$app->getView()->registerJs('Analytics.formats = '.json_encode($formats));
			Craft::$app->getView()->registerJs('Analytics.currency = '.json_encode($currency));*/
		}
	}

    public function registerCpUrlRules(RegisterUrlRulesEvent $event)
    {
        $rules = [
            'analytics/settings' => 'analytics/settings/index',
            'analytics/install' => 'analytics/install/index',
            'analytics/utils' => 'analytics/utils/metadata',
            'analytics/utils/metadata' => 'analytics/utils/metadata',
            'analytics/tests/dataTypes' => 'analytics/tests/dataTypes',
            'analytics/tests' => 'analytics/tests/columns',
            'analytics/tests/columns' => 'analytics/tests/columns',
            'analytics/tests/columnGroups' => 'analytics/tests/columnGroups',
            'analytics/tests/formatting' => 'analytics/tests/formatting',
            'analytics/tests/reportWidgets' => 'analytics/tests/reportWidgets',
        ];

        $event->rules = array_merge($event->rules, $rules);
    }

	/**
	 * Get Required Plugins
	 */
	public function getRequiredPlugins()
	{
		return array(
			array(
				'name' => "OAuth",
				'handle' => 'oauth',
				'url' => 'https://dukt.net/craft/oauth',
				'version' => '1.0.0'
			)
		);
	}

	/**
	 * On Before Uninstall
	 */
	public function onBeforeUninstall()
	{
		if(isset(\dukt\oauth\Plugin::getInstance()->oauth))
		{
			\dukt\oauth\Plugin::getInstance()->oauth->deleteTokensByPlugin('analytics');
		}
	}

    /**
     * Get Settings URL
     */
    public function getSettingsUrl()
    {
        return 'analytics/settings';
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
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
        $url = \craft\helpers\UrlHelper::cpUrl('analytics/settings');

        \Craft::$app->controller->redirect($url);

        return '';
    }

	// Protected Methods
	// =========================================================================

	/**
	 * Defined Settings
	 */
	protected function defineSettings()
	{
		return array(
            'accountId' => AttributeType::String,
            'accountName' => AttributeType::String,
            'webPropertyId' => AttributeType::String,
            'webPropertyName' => AttributeType::String,
			'internalWebPropertyId' => AttributeType::String,
            'profileId' => AttributeType::String,
            'profileName' => AttributeType::String,
            'profileCurrency' => AttributeType::String,
			'realtimeRefreshInterval' => array(AttributeType::Number, 'default' => 60),
			'forceConnect' => array(AttributeType::Bool, 'default' => false),
			'enableRealtime' => AttributeType::Bool,
			'tokenId' => AttributeType::Number,
		);
	}
}
