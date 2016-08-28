<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsPlugin extends BasePlugin
{
	// Public Methods
	// =========================================================================

	public function init()
	{
		require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

		parent::init();

		// social login for CP

		if (craft()->request->isCpRequest())
		{
			craft()->templates->includeJsResource('analytics/js/Analytics.js', true);

			$continents = craft()->analytics_metadata->getContinents();
			$subContinents = craft()->analytics_metadata->getSubContinents();
			$formats = ChartHelper::getFormats();

			craft()->templates->includeJs('Analytics.continents = '.json_encode($continents));
			craft()->templates->includeJs('Analytics.subContinents = '.json_encode($subContinents));
			craft()->templates->includeJs('Analytics.formats = '.json_encode($formats));
		}
	}

	/**
	 * Get Name
	 */
	public function getName()
	{
		return Craft::t('Analytics');
	}

   /**
	 * Get Description
	 */
	public function getDescription()
	{
		return Craft::t('Customizable statistics widgets and entry tracking for Google Analytics.');
	}

	/**
	 * Get Version
	 */
	public function getVersion()
	{
		return '3.3.1';
	}

	/**
	 * Get Schema Version
	 *
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '1.0.0';
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
	 * Get Developer
	 */
	public function getDeveloper()
	{
		return 'Dukt';
	}

	/**
	 * Get Developer URL
	 */
	public function getDeveloperUrl()
	{
		return 'https://dukt.net/';
	}

	/**
	 * Get Documentation URL
	 */
	public function getDocumentationUrl()
	{
		return 'https://dukt.net/craft/analytics/docs/';
	}

	/**
	 * Get Release Feed URL
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://dukt.net/craft/analytics/updates.json';
	}

	/**
	 * Get Settings URL
	 */
	public function getSettingsUrl()
	{
		return 'analytics/settings';
	}

	/**
	 * Hook Register CP Routes
	 */
	public function registerCpRoutes()
	{
		return array(
			'analytics/settings' => array('action' => "analytics/settings/index"),
			'analytics/install' => array('action' => "analytics/install/index"),
			'analytics/utils' => array('action' => "analytics/utils/metadata"),
			'analytics/utils/metadata' => array('action' => "analytics/utils/metadata"),
			'analytics/utils/dataTypes' => array('action' => "analytics/utils/dataTypes"),
			'analytics/utils/columns' => array('action' => "analytics/utils/columns"),
			'analytics/utils/columnGroups' => array('action' => "analytics/utils/columnGroups"),
			'analytics/utils/tests' => array('action' => "analytics/utils/tests"),
			'analytics/utils/charts' => array('action' => "analytics/utils/charts"),
		);
	}

	/**
	 * On Before Uninstall
	 */
	public function onBeforeUninstall()
	{
		if(isset(craft()->oauth))
		{
			craft()->oauth->deleteTokensByPlugin('analytics');
		}
	}

	// Protected Methods
	// =========================================================================

	/**
	 * Defined Settings
	 */
	protected function defineSettings()
	{
		return array(
			'webPropertyId' => array(AttributeType::String),
			'accountId' => array(AttributeType::String),
			'internalWebPropertyId' => array(AttributeType::String),
			'profileId' => array(AttributeType::String),
			'realtimeRefreshInterval' => array(AttributeType::Number, 'default' => 60),
			'enableRealtime' => array(AttributeType::Bool),
			'tokenId' => array(AttributeType::Number),
		);
	}
}
