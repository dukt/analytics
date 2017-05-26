<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
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
     */
    protected function getClient()
    {
        $token = Analytics::$plugin->getOauth()->getToken();

        if ($token) {
            // make token compatible with Google library
            $arrayToken = [
                'created' => 0,
                'access_token' => $token->getToken(),
                'expires_in' => $token->getExpires(),
            ];

            $arrayToken = json_encode($arrayToken);

            // client
            $client = new Google_Client();
            $client->setApplicationName('Google+ PHP Starter Application');
            $client->setClientId('clientId');
            $client->setClientSecret('clientSecret');
            $client->setRedirectUri('redirectUri');
            $client->setAccessToken($arrayToken);

            return $client;
        }
    }
}
