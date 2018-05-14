<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2018, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\Plugin as Analytics;
use yii\web\Response;

class OauthController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * OAuth connect.
     *
     * @return Response
     */
    public function actionConnect()
    {
        $provider = Analytics::$plugin->oauth->getOauthProvider();

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
     * OAuth disconnect.
     *
     * @return Response
     */
    public function actionDisconnect()
    {
        if (Analytics::$plugin->oauth->deleteToken()) {
            Analytics::$plugin->cache->delete(['accountExplorerData']);

            Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Disconnected from Google Analytics.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('analytics', 'Couldn’t disconnect from Google Analytics'));
        }


        // redirect

        $redirect = Craft::$app->getRequest()->referrer;

        return $this->redirect($redirect);
    }

    /**
     * OAuth callback.
     *
     * @return Response
     */
    public function actionCallback()
    {
        $provider = Analytics::$plugin->oauth->getOauthProvider();

        $code = Craft::$app->getRequest()->getParam('code');

        try {
            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);

            // Save token
            Analytics::$plugin->oauth->saveToken($token);

            // Todo: Reset session variables

            $pluginSettings = Analytics::$plugin->getSettings();

            if ($pluginSettings->forceConnect === true) {
                $pluginSettings->forceConnect = false;
                Craft::$app->getPlugins()->savePluginSettings(Analytics::$plugin, $pluginSettings->getAttributes());
            }

            // Redirect
            Craft::$app->getSession()->setNotice(Craft::t('analytics', 'Connected to Google Analytics.'));
        } catch (Exception $e) {
            // Failed to get the token credentials or user details.
            Craft::$app->getSession()->setError($e->getMessage());
        }

        return $this->redirect('analytics/settings');
    }
}
