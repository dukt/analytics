<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\services;

use yii\base\Component;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Google;
use craft\helpers\UrlHelper;
use dukt\analytics\Plugin as Analytics;

class Oauth extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Save Token
     *
     * @param AccessToken $token
     */
    public function saveToken(AccessToken $token)
    {
        $info = Analytics::getInstance()->getInfo();

        $info->token = [
            'accessToken' => $token->getToken(),
            'expires' => $token->getExpires(),
            'refreshToken' => $token->getRefreshToken(),
            'resourceOwnerId' => $token->getResourceOwnerId(),
            'values' => $token->getValues(),
        ];

        Analytics::getInstance()->saveInfo($info);
    }

    /**
     * Returns the OAuth Token.
     *
     * @param bool $refresh
     *
     * @return AccessToken|null
     */
    public function getToken($refresh = true)
    {
        $info = Analytics::getInstance()->getInfo();

        if (!$info->token) {
            return null;
        }

        $token = new AccessToken([
            'access_token' => $info->token['accessToken'] ?? null,
            'expires' => $info->token['expires'] ?? null,
            'refresh_token' => $info->token['refreshToken'] ?? null,
            'resource_owner_id' => $info->token['resourceOwnerId'] ?? null,
            'values' => $info->token['values'] ?? null,
        ]);

        if ($refresh && $token->hasExpired()) {
            $provider = $this->getOauthProvider();
            $grant = new \League\OAuth2\Client\Grant\RefreshToken();
            $newToken = $provider->getAccessToken($grant, ['refresh_token' => $token->getRefreshToken()]);

            $token = new AccessToken([
                'access_token' => $newToken->getToken(),
                'expires' => $newToken->getExpires(),
                'refresh_token' => $info->token['refreshToken'],
                'resource_owner_id' => $newToken->getResourceOwnerId(),
                'values' => $newToken->getValues(),
            ]);

            $this->saveToken($token);
        }

        return $token;
    }

    /**
     * Delete Token
     *
     * @return bool
     */
    public function deleteToken()
    {
        $info = Analytics::getInstance()->getInfo();
        $info->token = null;
        Analytics::getInstance()->saveInfo($info);
        return true;
    }

    /**
     * Returns a Twitter provider (server) object.
     *
     * @return Google
     */
    public function getOauthProvider()
    {
        $options = [
            'clientId' => Analytics::$plugin->getSettings()->oauthClientId,
            'clientSecret' => Analytics::$plugin->getSettings()->oauthClientSecret
        ];

        $options = array_merge($options, Analytics::$plugin->getSettings()->oauthProviderOptions);

        if (!isset($options['redirectUri'])) {
            $options['redirectUri'] = $this->getRedirectUri();
        }

        $options = array_map('Craft::parseEnv', $options);

        return new Google($options);
    }

    /**
     * Returns the javascript orgin URL.
     *
     * @return string
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getJavascriptOrigin()
    {
        $url = UrlHelper::baseUrl();

        return rtrim($url, '/');
    }

    /**
     * Returns the redirect URI.
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return UrlHelper::actionUrl('analytics/oauth/callback');
    }
}
