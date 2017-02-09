<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;

class OauthController extends Controller
{
	// Properties
	// =========================================================================

	/**
	 * @var string
	 */
	private $handle = 'google';

	/**
	 * @var array
	 */
	private $scope = array(
		'https://www.googleapis.com/auth/userinfo.profile',
		'https://www.googleapis.com/auth/userinfo.email',
		'https://www.googleapis.com/auth/analytics',
		'https://www.googleapis.com/auth/analytics.edit',
	);

	/**
	 * @var array
	 */
	private $authorizationOptions = array(
		'access_type' => 'offline',
		'approval_prompt' => 'force'
	);

	// Public Methods
	// =========================================================================

	/**
	 * Connect
	 *
	 * @return null
	 */
	public function actionConnect()
	{
		// referer

		$referer = Craft::$app->getSession()->get('analytics.referer');

		if (!$referer)
		{
			$referer = Craft::$app->request->referrer;

			Craft::$app->getSession()->set('analytics.referer', $referer);

			// \dukt\analytics\Plugin::log('OAuth Connect Referer: '.$referer, LogLevel::Info);
		}


		// connect

		if ($response = \dukt\oauth\Plugin::getInstance()->oauth->connect(array(
			'plugin'   => 'analytics',
			'provider' => $this->handle,
			'scope'   => $this->scope,
			'authorizationOptions'   => $this->authorizationOptions
		)))
		{
            if($response && is_object($response) && !$response->data)
            {
                return $response;
            }

			if ($response['success'])
			{
				// token
				$token = $response['token'];

				// save token
				\dukt\analytics\Plugin::getInstance()->analytics_oauth->saveToken($token);


                // Reset forceConnect plugin setting

                $plugin = Craft::$app->plugins->getPlugin('analytics');
                $settings = $plugin->getSettings();

/*                if($settings['forceConnect'] === true)
                {
                    $settings['forceConnect'] = false;
                    Craft::$app->plugins->savePluginSettings($plugin, $settings);
                }*/

				if($token)
				{
					// \dukt\analytics\Plugin::log('Token: '."\r\n".print_r($token->getAttributes(), true), LogLevel::Info);
				}
				else
				{
					// \dukt\analytics\Plugin::log('Couldn’t get token', LogLevel::Error);
				}

				// session notice
				Craft::$app->getSession()->setNotice(Craft::t('app', "Connected to Google Analytics."));
			}
			else
			{
				// session error
				Craft::$app->getSession()->setError(Craft::t('app', $response['errorMsg']));
			}
		}
		else
		{
			// session error
			Craft::$app->getSession()->setError(Craft::t('app', "Couldn’t connect"));
		}

		// OAuth Step 5

		// redirect

		Craft::$app->getSession()->remove('analytics.referer');

		return $this->redirect($referer);
	}

	/**
	 * Disconnect
	 *
	 * @return null
	 */
	public function actionDisconnect()
	{
		if (\dukt\analytics\Plugin::getInstance()->analytics_oauth->deleteToken())
		{
            \dukt\analytics\Plugin::getInstance()->analytics_cache->delete(['accountExplorerData']);

			Craft::$app->getSession()->setNotice(Craft::t('app', "Disconnected from Google Analytics."));
		}
		else
		{
			Craft::$app->getSession()->setError(Craft::t('app', "Couldn’t disconnect from Google Analytics"));
		}


		// redirect

        $redirect = Craft::$app->request->referrer;

		return $this->redirect($redirect);
	}
}
