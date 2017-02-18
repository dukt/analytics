<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\Plugin as Analytics;

class OauthController extends Controller
{
	// Public Methods
	// =========================================================================

    /**
     * Connect
     *
     * @return null
     */
    public function actionConnect()
    {
        $provider = Analytics::$plugin->analytics_oauth->getOauthProvider();

        Craft::$app->getSession()->set('analytics.oauthState', $provider->getState());

        $authorizationUrl = $provider->getAuthorizationUrl([
            'scope' => [
                'https://www.googleapis.com/auth/userinfo.profile',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/analytics',
                'https://www.googleapis.com/auth/analytics.edit',
            ],
            'access_type' => 'offline',
            'approval_prompt' => 'force'
        ]);

        return $this->redirect($authorizationUrl);
    }

	/**
	 * Disconnect
	 *
	 * @return null
	 */
	public function actionDisconnect()
	{
		if (Analytics::$plugin->analytics_oauth->deleteToken())
		{
            Analytics::$plugin->analytics_cache->delete(['accountExplorerData']);

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

    /**
     * Callback
     *
     * @return null
     */
    public function actionCallback()
    {
        $provider = Analytics::$plugin->analytics_oauth->getOauthProvider();

        $code = Craft::$app->request->getParam('code');

        try {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            // Save token
            Analytics::$plugin->analytics_oauth->saveToken($token);

            // Reset session variables

            // Redirect
            Craft::$app->getSession()->setNotice(Craft::t('analytics', "Connected to Google Analytics."));

        } catch (Exception $e) {
            // Failed to get the token credentials or user details.
            Craft::$app->getSession()->setError($e->getMessage());
        }

        return $this->redirect('analytics/settings');
    }
}
