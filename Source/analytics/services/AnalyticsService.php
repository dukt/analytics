<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class AnalyticsService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $oauthHandle = 'google';
    private $token;

    // Public Methods
    // =========================================================================

    public function getCacheKey($key, array $request)
    {
        $dataSourceClassName = 'GoogleAnalytics';

        unset($request['CRAFT_CSRF_TOKEN']);

        $hash = md5(serialize($request));

        $cacheKey = 'analytics.'.$key.'.'.$dataSourceClassName.'.getChartData.'.$hash;

        return $cacheKey;
    }

    /**
     * Get data soruce from its class name
     */
    public function getDataSource($className)
    {
        $nsClassName = "\\Dukt\\Analytics\\DataSources\\$className";
        return new $nsClassName;
    }

    /**
     * Get Profile
     */
    public function getProfile()
    {
        $r = array();

        $webProperty = $this->getWebProperty();

        $profile = craft()->cache->get('analytics.profile');

        if(!$profile && !empty($webProperty['accountId']))
        {
            $profiles = craft()->analytics_api->managementProfiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

            $profile = $profiles['items'][0];

            craft()->cache->set('analytics.profile', $profile);
        }

        if($profile)
        {
            return $profile;
        }
        else
        {
            throw new Exception("Couldn't get profile");
        }

        return $r;
    }

    /**
     * Get Web Property
     */
    public function getWebProperty()
    {
        $r = array();

        try
        {
            $webProperty = craft()->cache->get('analytics.webProperty');

            if(!$webProperty)
            {
                $webProperties = craft()->analytics_api->managementWebproperties->listManagementWebproperties("~all");

                foreach($webProperties['items'] as $webPropertyItem)
                {
                    if($webPropertyItem['id'] == $this->getSetting('profileId'))
                    {
                        $webProperty = $webPropertyItem;
                    }
                }

                if($webProperty)
                {
                    craft()->cache->set('analytics.webProperty', $webProperty);
                }
            }

            $r = $webProperty;

        }
        catch(\Exception $e)
        {
            $r['error'] = $e->getMessage();
        }

        return $r;
    }

    /**
     * Get Properties Opts
     */
    public function getPropertiesOpts()
    {
        $properties = array("" => Craft::t("Select"));

        $response = craft()->analytics_api->managementWebproperties->listManagementWebproperties("~all");

        if(!$response)
        {
            Craft::log(__METHOD__.' : Could not list management web properties', LogLevel::Info, true);
            return false;
        }

        $items = $response['items'];

        foreach($items as $item)
        {
            $name = $item['id'];

            if(!empty($item['websiteUrl']))
            {
                $name .= ' - '.$item['websiteUrl'];
            }
            elseif(!empty($item['name']))
            {
                $name .= ' - '.$item['name'];
            }

            $properties[$item['id']] = $name;
        }

        return $properties;
    }

    /**
     * Get Setting
     *
     * @param string $key
     */
    public function getSetting($key)
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        return $settings[$key];
    }

    /**
     * Get Language
     */
    public function getLanguage()
    {
        return craft()->language;
    }

    /**
     * Send Request
     */
    public function sendRequest(Analytics_RequestCriteriaModel $criteria)
    {
        $profile = craft()->analytics->getProfile();

        $criteria->ids = 'ga:'.$profile['id'];

        if($criteria->realtime)
        {
            $response = craft()->analytics_api->apiGetGADataRealtime(
                $criteria->ids,
                $criteria->metrics,
                $criteria->optParams,
                $criteria->enableCache
            );
        }
        else
        {
            $response = craft()->analytics_api->apiGetGAData(
                $criteria->ids,
                $criteria->startDate,
                $criteria->endDate,
                $criteria->metrics,
                $criteria->optParams,
                $criteria->enableCache
            );
        }

        if($criteria->format == 'gaData')
        {
            return $response;
        }
        else
        {
            return AnalyticsHelper::gaDataToArray($response);
        }
    }

    /**
     * Get Element URL Path
     *
     * @param int           $elementId
     * @param string|null   $localeId
     */
    public function getElementUrlPath($elementId, $localeId)
    {
        $element = craft()->elements->getElementById($elementId, null, $localeId);

        $uri = $element->uri;
        $url = $element->url;

        $components = parse_url($url);

        if($components['path'])
        {
            $uri = $components['path'];
        }

        return $uri;
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
