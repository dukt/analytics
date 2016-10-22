<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use \Google_Client;
use \Google_Service_Analytics;

class Analytics_ApiService extends BaseApplicationComponent
{
	// Public Methods
	// =========================================================================

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->googleAnalytics()->metadata_columns->listMetadataColumns('ga');
    }

    /**
     * Get Account Explorer Data
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
     * Sends a request based on Analytics_RequestCriteriaModel to Google Analytics' API.
     *
     * @param Analytics_RequestCriteriaModel $criteria
     *
     * @return string
     */
    public function sendRequest(Analytics_RequestCriteriaModel $criteria)
    {
        if($criteria->realtime)
        {
            return $this->getRealtimeReport($criteria);
        }
        else
        {
            return $this->getReport($criteria);
        }
    }

	// Private Methods
	// =========================================================================

    /**
     * Populate Criteria
     *
     * @param Analytics_RequestCriteriaModel $criteria
     */
    private function populateCriteria(Analytics_RequestCriteriaModel $criteria)
    {
        // Profile ID

        $criteria->ids = craft()->analytics->getProfileId();


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

        $configFilters = craft()->config->get('filters', 'analytics');

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
     * @param Analytics_RequestCriteriaModel $criteria
     *
     * @return string
     */
    private function getReport(Analytics_RequestCriteriaModel $criteria)
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
        $response = craft()->analytics_cache->get($cacheId);

        if(!$response)
        {
            $response = $this->googleAnalytics()->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);
            craft()->analytics_cache->set($cacheId, $response, null, null, $enableCache);
        }

        return $this->parseReportResponse($response);
    }

    /**
     * Returns a Realtime Report from criteria
     *
     * @param Analytics_RequestCriteriaModel $criteria
     *
     * @return string
     */
    private function getRealtimeReport(Analytics_RequestCriteriaModel $criteria)
    {
        $this->populateCriteria($criteria);

        $ids = $criteria->ids;
        $metrics = $criteria->metrics;
        $optParams = $criteria->optParams;

        $cacheDuration = craft()->analytics->getRealtimeRefreshInterval();

        $cacheId = ['api.apiGetGADataRealtime', $ids, $metrics, $optParams];
        $response = craft()->analytics_cache->get($cacheId);

        if(!$response)
        {
            $response = $this->googleAnalytics()->data_realtime->get($ids, $metrics, $optParams);

            craft()->analytics_cache->set($cacheId, $response, $cacheDuration);
        }

        return $this->parseReportResponse($response);
    }

	/**
	 * Parse Report Response
	 * 
	 * @param $data
	 *
	 * @return array
	 */
	private function parseReportResponse($data)
	{
		// Columns

		$cols = [];

		foreach($data->columnHeaders as $col)
		{
			$dataType = $col->dataType;
			$id = $col->name;
			$label = craft()->analytics_metadata->getDimMet($col->name);
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
				'label' => Craft::t($label),
			);
		}


		// Rows

		$rows = [];

		if($data->rows)
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
						$value = craft()->analytics_metadata->getContinentCode($value);
					}

					if($col['id'] == 'ga:subContinent')
					{
						$value = craft()->analytics_metadata->getSubContinentCode($value);
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
							$value = Craft::t($value);
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
     * Returns the Google Analytics API object
     *
     * @return bool|Google_Service_Analytics
     */
    private function googleAnalytics()
    {
        $client = $this->getClient();

        return new Google_Service_Analytics($client);
    }

    /**
     * Returns a Google client
     *
     * @return bool|Google_Client
     */
    private function getClient()
    {
        $provider = craft()->oauth->getProvider('google');

        if($provider)
        {
            $token = craft()->analytics_oauth->getToken();

            if ($token)
            {
                // make token compatible with Google library
                $arrayToken = array(
                    'created' => 0,
                    'access_token' => $token->accessToken,
                    'expires_in' => $token->endOfLife,
                );

                $arrayToken = json_encode($arrayToken);

                $client = new Google_Client();
                $client->setApplicationName('Google+ PHP Starter Application');
                $client->setClientId('clientId');
                $client->setClientSecret('clientSecret');
                $client->setRedirectUri('redirectUri');
                $client->setAccessToken($arrayToken);

                return $client;
            }
            else
            {
                AnalyticsPlugin::log('Undefined token', LogLevel::Error);
                return false;
            }
        }
        else
        {
            AnalyticsPlugin::log('Couldn’t get connect provider', LogLevel::Error);
            return false;
        }
    }
}
