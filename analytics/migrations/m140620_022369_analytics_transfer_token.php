<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140620_022369_analytics_transfer_token extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $this->transferSystemToken('analytics.system');

		return true;
	}

    private function saveToken($token)
    {
        craft()->analytics->saveToken($token);
    }

    private function transferSystemToken($namespace)
    {
        try {

            if(file_exists(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php'))
            {
                require_once(CRAFT_PLUGINS_PATH.'oauth/vendor/autoload.php');
            }

            if(class_exists('Craft\Oauth_TokenRecord') && class_exists('OAuth\OAuth2\Token\StdOAuth2Token'))
            {
                // get token record

                $record = Oauth_TokenRecord::model()->find(
                    'namespace=:namespace',
                    array(
                        ':namespace' => $namespace
                    )
                );

                if($record)
                {
                    // transform token

                    $token = @unserialize(base64_decode($record->token));

                    if($token)
                    {
                        // oauth 2
                        $newToken = new \OAuth\OAuth2\Token\StdOAuth2Token();
                        $newToken->setAccessToken($token->access_token);
                        $newToken->setLifeTime($token->expires);

                        if (isset($token->refresh_token))
                        {
                            $newToken->setRefreshToken($token->refresh_token);
                        }

                        $this->saveToken($newToken);
                    }
                    else
                    {
                        Craft::log('Token error.', LogLevel::Info, true);
                    }
                }
                else
                {
                    Craft::log('Token record error.', LogLevel::Info, true);
                }
            }
            else
            {
                Craft::log('Class error.', LogLevel::Info, true);
            }
        }
        catch(\Exception $e)
        {
            Craft::log($e->getMessage(), LogLevel::Info, true);
        }
    }
}
