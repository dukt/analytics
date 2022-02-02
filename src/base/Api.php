<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\base;

use craft\base\Component;
use Google_Client;
use dukt\analytics\Plugin as Analytics;

abstract class Api extends Component
{
    /**
     * Returns a Google client.
     *
     * @return Google_Client
     * @throws \yii\base\InvalidConfigException
     */
    protected function getClient()
    {
        $token = Analytics::$plugin->getOauth()->getToken();

        if ($token) {
            // make token compatible with Google Client

            $arrayToken = json_encode([
                'created' => 0,
                'access_token' => $token->getToken(),
                'expires_in' => $token->getExpires(),
            ]);

            // client
            $client = new Google_Client();
            $client->setApplicationName('Google+ PHP Starter Application');
            $client->setClientId('clientId');
            $client->setClientSecret('clientSecret');
            $client->setRedirectUri('redirectUri');
            $client->setAccessToken($arrayToken);

            return $client;
        }

        return null;
    }
}
