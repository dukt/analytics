<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use dukt\oauth\models\Token;

class Oauth extends Component
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
		$provider = \dukt\oauth\Plugin::getInstance()->oauth->getProvider('google');

		if ($provider && $provider->isConfigured())
		{
			return true;
		}
		else
		{
			$url = UrlHelper::getUrl('analytics/install');
			Craft::$app->request->redirect($url);
			return false;
		}
	}

	/**
	 * Save Token
	 *
	 * @param Token $token
	 */
    public function saveToken(Token $token)
    {
        // get plugin
        $plugin = Craft::$app->plugins->getPlugin('analytics');

        // get settings
        $settings = $plugin->getSettings();


        // do we have an existing token ?

        $existingToken = \dukt\oauth\Plugin::getInstance()->oauth->getTokenById($settings->tokenId);

        if($existingToken)
        {
            $token->id = $existingToken->id;
        }

        // save token
        \dukt\oauth\Plugin::getInstance()->oauth->saveToken($token);

        // set token ID
        $settings->tokenId = $token->id;

        // save plugin settings
        Craft::$app->plugins->savePluginSettings($plugin, $settings->getAttributes());
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
			$plugin = Craft::$app->plugins->getPlugin('analytics');

			// get settings
			$settings = $plugin->getSettings();

			// get tokenId
			$tokenId = $settings->tokenId;

			// get token
			$token = \dukt\oauth\Plugin::getInstance()->oauth->getTokenById($tokenId);

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
		$plugin = Craft::$app->plugins->getPlugin('analytics');

		// get settings
		$settings = $plugin->getSettings();

		if($settings->tokenId)
		{
			$token = \dukt\oauth\Plugin::getInstance()->oauth->getTokenById($settings->tokenId);

			if($token)
			{
				if(\dukt\oauth\Plugin::getInstance()->oauth->deleteToken($token))
				{
					$settings->tokenId = null;

					Craft::$app->plugins->savePluginSettings($plugin, $settings);

					return true;
				}
			}
		}

		return false;
	}
}
