<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_OauthService extends BaseApplicationComponent
{
	// Properties
	// =========================================================================

	private $token;

	// Public Methods
	// =========================================================================

	/**
	 * Require OAuth with configured provider
	 *
	 * @return bool
	 */
	public function requireOauth()
	{
		$provider = craft()->oauth->getProvider('google');

		if ($provider && $provider->isConfigured())
		{
			return true;
		}
		else
		{
			$url = UrlHelper::getUrl('analytics/install');
			craft()->request->redirect($url);
			return false;
		}
	}

	/**
	 * Save Token
	 *
	 * @param Oauth_TokenModel $token
	 */
	public function saveToken(Oauth_TokenModel $token)
	{
		// get plugin
		$plugin = craft()->plugins->getPlugin('analytics');

		// get settings
		$settings = $plugin->getSettings();


		// do we have an existing token ?

		$existingToken = craft()->oauth->getTokenById($settings->tokenId);

		if($existingToken)
		{
			$token->id = $existingToken->id;
		}

		// save token
		craft()->oauth->saveToken($token);

		// set token ID
		$settings->tokenId = $token->id;

		// save plugin settings
		craft()->plugins->savePluginSettings($plugin, $settings);
	}

	/**
	 * Get OAuth Token
	 *
	 * @return mixed
	 */
	public function getToken()
	{
		if($this->token)
		{
			return $this->token;
		}
		else
		{
			// get plugin
			$plugin = craft()->plugins->getPlugin('analytics');

			// get settings
			$settings = $plugin->getSettings();

			// get tokenId
			$tokenId = $settings->tokenId;

			// get token
			$token = craft()->oauth->getTokenById($tokenId);

			return $token;
		}
	}

	/**
	 * Delete Token
	 *
	 * @return bool
	 */
	public function deleteToken()
	{
		// get plugin
		$plugin = craft()->plugins->getPlugin('analytics');

		// get settings
		$settings = $plugin->getSettings();

		if($settings->tokenId)
		{
			$token = craft()->oauth->getTokenById($settings->tokenId);

			if($token)
			{
				if(craft()->oauth->deleteToken($token))
				{
					$settings->tokenId = null;

					craft()->plugins->savePluginSettings($plugin, $settings);

					return true;
				}
			}
		}

		return false;
	}
}
