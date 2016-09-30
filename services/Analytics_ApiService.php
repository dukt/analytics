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

	private $oauthHandle = 'google';
	private $webProperties;

	// Public Methods
	// =========================================================================

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function getColumns()
	{
		return $this->getGoogleAnalyticsService()->metadata_columns->listMetadataColumns('ga');
	}

	/**
	 * Get accounts
	 *
	 * @param $optParams
	 *
	 * @return array
	 */
	public function getAccounts($optParams = array())
	{
		return $this->getGoogleAnalyticsService()->management_accounts->listManagementAccounts($optParams);
	}

	/**
	 * Returns all web properties
	 *
	 * @return bool
	 */
	public function getProperties()
	{
        return $this->getGoogleAnalyticsService()->management_webproperties->listManagementWebproperties("~all");
	}

	/**
	 * Returns all web properties
	 *
	 * @return bool
	 */
	public function getWebProperties()
	{
		if(!$this->webProperties)
		{
			$response = $this->getGoogleAnalyticsService()->management_webproperties->listManagementWebproperties("~all");

			if(!$response)
			{
				AnalyticsPlugin::log('Could not list management web properties', LogLevel::Error);
				return false;
			}

			$this->webProperties = $response['items'];
		}

		return $this->webProperties;
	}

	/**
	 * Returns the web property from its ID
	 *
	 * @param $webPropertyId
	 *
	 * @return mixed
	 */
	public function getWebProperty($webPropertyId)
	{
		foreach($this->getWebProperties() as $webProperty)
		{
			if($webProperty->id == $webPropertyId)
			{
				return $webProperty;
			}
		}
	}

	/**
	 * Get profiles
	 *
	 * @param $accountId
	 * @param $webPropertyId
	 *
	 * @return array
	 */
	public function getProfiles($accountId = '~all', $webPropertyId = '~all')
	{
		return $this->getGoogleAnalyticsService()->management_profiles->listManagementProfiles($accountId, $webPropertyId);
	}

	/**
	 * Get Report
	 * @param       $ids
	 * @param       $startDate
	 * @param       $endDate
	 * @param       $metrics
	 * @param array $optParams
	 * @param bool  $enableCache
	 *
	 * @return array
	 */
	public function getReport($ids, $startDate, $endDate, $metrics, $optParams = array(), $enableCache = true)
	{
		$request = [$ids, $startDate, $endDate, $metrics, $optParams];

		$cacheId = ['api.apiGetGAData', $request];
		$response = craft()->analytics_cache->get($cacheId);

		if(!$response)
		{
			$response = $this->sendReportRequest($ids, $startDate, $endDate, $metrics, $optParams);
			craft()->analytics_cache->set($cacheId, $response, null, null, $enableCache);
		}

		return $this->parseReportResponse($response);
	}

	/**
	 * Get real-time report
	 * @param       $ids
	 * @param       $metrics
	 * @param array $optParams
	 *
	 * @return array
	 */
	public function getRealtimeReport($ids, $metrics, $optParams = array())
	{
		$plugin = craft()->plugins->getPlugin('analytics');

		$settings = $plugin->getSettings();

		$cacheDuration = craft()->analytics->getRealtimeRefreshInterval();

		$cacheId = ['api.apiGetGADataRealtime', $ids, $metrics, $optParams];
		$response = craft()->analytics_cache->get($cacheId);

		if(!$response)
		{
			$response = $this->sendRealtimeReportRequest($ids, $metrics, $optParams);

			craft()->analytics_cache->set($cacheId, $response, $cacheDuration);
		}

		return $this->parseReportResponse($response);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns a API object
	 *
	 * @return bool|Google_Service_Analytics
	 */
	private function getGoogleAnalyticsService()
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
		$handle = $this->oauthHandle;

		$provider = craft()->oauth->getProvider($handle);

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


	/**
	 * Returns Analytics data for a view (profile). (ga.get)
	 *
	 * @param string $ids Unique table ID for retrieving Analytics data. Table ID is of the form ga:XXXX, where XXXX is the Analytics view (profile) ID.
	 * @param string $startDate Start date for fetching Analytics data. Requests can specify a start date formatted as YYYY-MM-DD, or as a relative date (e.g., today, yesterday, or 7daysAgo). The default value is 7daysAgo.
	 * @param string $endDate End date for fetching Analytics data. Request can should specify an end date formatted as YYYY-MM-DD, or as a relative date (e.g., today, yesterday, or 7daysAgo). The default value is yesterday.
	 * @param string $metrics A comma-separated list of Analytics metrics. E.g., 'ga:sessions,ga:pageviews'. At least one metric must be specified.
	 * @param array $optParams Optional parameters.
	 *
	 * @opt_param int max-results The maximum number of entries to include in this feed.
	 * @opt_param string sort A comma-separated list of dimensions or metrics that determine the sort order for Analytics data.
	 * @opt_param string dimensions A comma-separated list of Analytics dimensions. E.g., 'ga:browser,ga:city'.
	 * @opt_param int start-index An index of the first entity to retrieve. Use this parameter as a pagination mechanism along with the max-results parameter.
	 * @opt_param string segment An Analytics segment to be applied to data.
	 * @opt_param string samplingLevel The desired sampling level.
	 * @opt_param string filters A comma-separated list of dimension or metric filters to be applied to Analytics data.
	 * @opt_param string output The selected format for the response. Default format is JSON.
	 *
	 * @param bool $enableCache Caches the API response when set to 'true'. Default value is 'true'.
	 *
	 * @return Google_Service_Analytics_GaData
	 */
	private function sendReportRequest($ids, $startDate, $endDate, $metrics, $optParams)
	{
		return $this->getGoogleAnalyticsService()->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);
	}

	/**
	 * Returns real time data for a view (profile).
	 *
	 * @param string $ids Unique table ID for retrieving real time data. Table ID is of the form ga:XXXX, where XXXX is the Analytics view (profile) ID.
	 * @param string $p2 A comma-separated list of real time metrics. E.g., 'rt:activeUsers'. At least one metric must be specified.
	 * @param array $optParams Optional parameters.
	 *
	 * @opt_param int max-results The maximum number of entries to include in this feed.
	 * @opt_param string sort A comma-separated list of dimensions or metrics that determine the sort order for real time data.
	 * @opt_param string dimensions A comma-separated list of real time dimensions. E.g., 'rt:medium,rt:city'.
	 * @opt_param string filters A comma-separated list of dimension or metric filters to be applied to real time data.
	 *
	 * @param bool $enableCache Caches the API response when set to 'true'. Default value is 'true'.
	 *
	 * @return Google_Service_Analytics_RealtimeData
	 */
	private function sendRealtimeReportRequest($ids, $metrics, $optParams)
	{
		return $this->getGoogleAnalyticsService()->data_realtime->get($ids, $metrics, $optParams);
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
}
