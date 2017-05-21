<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use \Google_Client;
use \Google_Service_Analytics;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\Plugin as Analytics;

class AnalyticsApi extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get columns.
     *
     * @return \Google_Service_Analytics_Columns
     */
    public function getColumns()
    {
        return $this->googleAnalytics()->metadata_columns->listMetadataColumns('ga');
    }

    /**
     * Get account explorer data.
     *
     * @return array
     */
    public function getAccountExplorerData()
    {
        // Accounts
        $apiAccounts = $this->googleAnalytics()->management_accounts->listManagementAccounts();
        $accounts = $apiAccounts->toSimpleObject()->items;

        // Properties
        $apiProperties = $this->googleAnalytics()->management_webproperties->listManagementWebproperties('~all');;
        $properties = $apiProperties->toSimpleObject()->items;

        // Views
        $apiViews = $this->googleAnalytics()->management_profiles->listManagementProfiles('~all', '~all');
        $views = $apiViews->toSimpleObject()->items;

        // Return Data
        return [
            'accounts' => $accounts,
            'properties' => $properties,
            'views' => $views,
        ];
    }

    /**
     * Populate Account Explorer Settings
     *
     * @param array $settings
     *
     * @return array
     */
    public function populateAccountExplorerSettings($settings = array())
    {
        if(!empty($settings['accountId']) && !empty($settings['webPropertyId']) && !empty($settings['profileId']))
        {
            $apiAccounts = $this->googleAnalytics()->management_accounts->listManagementAccounts();

            $account = null;

            foreach($apiAccounts as $apiAccount)
            {
                if($apiAccount->id == $settings['accountId'])
                {
                    $account = $apiAccount;
                }
            }

            $webProperty = $this->googleAnalytics()->management_webproperties->get($settings['accountId'], $settings['webPropertyId']);
            $profile = $this->googleAnalytics()->management_profiles->get($settings['accountId'], $settings['webPropertyId'], $settings['profileId']);

            $settings['accountName'] = $account->name;
            $settings['webPropertyName'] = $webProperty->name;
            $settings['internalWebPropertyId'] = $webProperty->internalWebPropertyId;
            $settings['profileCurrency'] = $profile->currency;
            $settings['profileName'] = $profile->name;
        }

        return $settings;
    }

    /**
     * Parse Report Response
     *
     * @param $data
     *
     * @return array
     */
    public function parseReportResponse($data)
    {
        // Columns

        $cols = [];

        foreach($data['columnHeaders'] as $col)
        {
            $dataType = $col->dataType;
            $id = $col->name;
            $label = Analytics::$plugin->metadata->getDimMet($col->name);
            $type = strtolower($dataType);

            switch($col->name)
            {
                case 'ga:date':
                case 'ga:yearMonth':
                    $type = 'date';
                    break;

                case 'ga:continent':
                    $type = 'continent';
                    break;
                case 'ga:subContinent':
                    $type = 'subContinent';
                    break;

                case 'ga:latitude':
                case 'ga:longitude':
                    $type = 'float';
                    break;
            }

            $cols[] = array(
                'type' => $type,
                'dataType' => $dataType,
                'id' => $id,
                'label' => Craft::t('analytics', $label),
            );
        }


        // Rows

        $rows = [];

        if($data['rows'])
        {
            $rows = $data->rows;

            foreach($rows as $kRow => $row)
            {
                foreach($row as $_valueKey => $_value)
                {
                    $col = $cols[$_valueKey];

                    $value = $this->formatRawValue($col['type'], $_value);

                    if($col['id'] == 'ga:continent')
                    {
                        $value = Analytics::$plugin->metadata->getContinentCode($value);
                    }

                    if($col['id'] == 'ga:subContinent')
                    {
                        $value = Analytics::$plugin->metadata->getSubContinentCode($value);
                    }


                    // translate values

                    switch($col['id'])
                    {
                        case 'ga:country':
                        case 'ga:city':
                            // case 'ga:continent':
                            // case 'ga:subContinent':
                        case 'ga:userType':
                        case 'ga:javaEnabled':
                        case 'ga:deviceCategory':
                        case 'ga:mobileInputSelector':
                        case 'ga:channelGrouping':
                        case 'ga:medium':
                            $value = Craft::t('analytics', $value);
                            break;
                    }


                    // update cell

                    $rows[$kRow][$_valueKey] = $value;
                }
            }
        }

        return array(
            'cols' => $cols,
            'rows' => $rows
        );
    }

    /**
     * Returns the Google Analytics API object (API v3)
     *
     * @return bool|Google_Service_Analytics
     */
    public function googleAnalytics()
    {
        $client = $this->getClient();

        return new Google_Service_Analytics($client);
    }

    /**
     * Sends a request based on RequestCriteria to Google Analytics' API.
     *
     * @param RequestCriteria $criteria
     *
     * @return array
     */
    public function sendRequest(RequestCriteria $criteria)
    {
        if ($criteria->realtime) {
            return $this->getRealtimeReport($criteria);
        }

        return $this->getReport($criteria);
    }

    // Private Methods
    // =========================================================================

    /**
     * Populates criteria’s ids, optParams.filters attributes with default profileId and filters
     *
     * @param RequestCriteria $criteria
     */
    private function populateCriteria(RequestCriteria $criteria)
    {
        // Profile ID

        $criteria->ids = Analytics::$plugin->getAnalytics()->getProfileId();


        // Filters

        $filters = [];

        if(isset($criteria->optParams['filters']))
        {
            $filters = $criteria->optParams['filters'];

            if(is_string($filters))
            {
                $filters = explode(";", $filters);
            }
        }

        $configFilters = Analytics::$plugin->getSettings()->filters;

        if($configFilters)
        {
            $filters = array_merge($filters, $configFilters);
        }

        if(count($filters) > 0)
        {
            $optParams = $criteria->optParams;

            $optParams['filters'] = implode(";", $filters);

            $criteria->optParams = $optParams;
        }
    }

    /**
     * Returns a GA Report from criteria
     *
     * @param RequestCriteria $criteria
     *
     * @return array
     */
    private function getReport(RequestCriteria $criteria)
    {
        $this->populateCriteria($criteria);

        $ids = $criteria->ids;
        $startDate = $criteria->startDate;
        $endDate = $criteria->endDate;
        $metrics = $criteria->metrics;
        $optParams = $criteria->optParams;
        $enableCache = $criteria->enableCache;

        $request = [$ids, $startDate, $endDate, $metrics, $optParams];

        $cacheId = ['api.apiGetGAData', $request];
        $response = Analytics::$plugin->cache->get($cacheId);

        if(!$response)
        {
            if(!$optParams)
            {
                $optParams = [];
            }

            $response = $this->googleAnalytics()->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);
            Analytics::$plugin->cache->set($cacheId, $response, null, null, $enableCache);
        }

        return $this->parseReportResponse($response);
    }

    /**
     * Returns a Realtime Report from criteria
     *
     * @param RequestCriteria $criteria
     *
     * @return array
     */
    private function getRealtimeReport(RequestCriteria $criteria)
    {
        $this->populateCriteria($criteria);

        $ids = $criteria->ids;
        $metrics = $criteria->metrics;
        $optParams = $criteria->optParams;

        $cacheDuration = Analytics::$plugin->getAnalytics()->getRealtimeRefreshInterval();

        $cacheId = ['api.apiGetGADataRealtime', $ids, $metrics, $optParams];
        $response = Analytics::$plugin->cache->get($cacheId);

        if(!$response)
        {
            $response = $this->googleAnalytics()->data_realtime->get($ids, $metrics, $optParams);

            Analytics::$plugin->cache->set($cacheId, $response, $cacheDuration);
        }

        return $this->parseReportResponse($response);
    }

    /**
     * Format RAW value
     *
     * @param string $type
     * @param string $value
     */
    private function formatRawValue($type, $value)
    {
        switch($type)
        {
            case 'integer':
            case 'currency':
            case 'float':
            case 'time':
            case 'percent':
                $value = (float) $value;
                break;

            default:
                $value = (string) $value;
        }

        return $value;
    }

    /**
     * Returns a Google client
     *
     * @return null|Google_Client
     */
    private function getClient()
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
