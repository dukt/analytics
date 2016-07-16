<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

class AnalyticsPlugin extends BasePlugin
{
	// Public Methods
	// =========================================================================

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
			'analytics/utils/columnGroups' => array('action' => "analytics/utils/columnGroups"),
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
