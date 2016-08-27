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
	 * Get profiles
	 *
	 * @param $webProperty
	 *
	 * @return mixed
	 */
	public function getProfiles($webProperty)
	{
		if($webProperty)
		{
			$response = craft()->analytics_api->managementProfiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

			return $response['items'];
		}
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
			$response = craft()->analytics_api->managementWebproperties->listManagementWebproperties("~all");

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
	public function getReport($ids, $startDate, $endDate, $metrics, $optParams = array(), $enableCache = true)
	{
		$api = craft()->analytics_api->getDataGa();

		$request = [$ids, $startDate, $endDate, $metrics, $optParams];

		$cacheId = ['api.apiGetGAData', $request];
		$response = craft()->analytics_cache->get($cacheId);

		if(!$response)
		{
			$response = $api->get($ids, $startDate, $endDate, $metrics, $optParams);
			craft()->analytics_cache->set($cacheId, $response, null, null, $enableCache);
		}

		return $this->parseGoogleAnalyticsResponse($response);
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
	public function getRealtimeReport($ids, $metrics, $optParams = array())
	{
		$plugin = craft()->plugins->getPlugin('analytics');

		$settings = $plugin->getSettings();

		$cacheDuration = craft()->analytics->getRealtimeRefreshInterval();

		$cacheId = ['api.apiGetGADataRealtime', $ids, $metrics, $optParams];
		$response = craft()->analytics_cache->get($cacheId);

		if(!$response)
		{
			$api = craft()->analytics_api->getDataRealtime();

			if($api)
			{
				$response = $api->get($ids, $metrics, $optParams);

				craft()->analytics_cache->set($cacheId, $response, $cacheDuration);
			}
		}

		return $this->parseGoogleAnalyticsResponse($response);
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
			case 'INTEGER':
			case 'CURRENCY':
			case 'FLOAT':
			case 'TIME':
			case 'PERCENT':
				$value = (float) $value;
				break;

			default:
				$value = (string) $value;
		}

		return $value;
	}

	/**
	 * Format Value
	 *
	 * @param string $type
	 * @param string $value
	 */
	private function formatValue($type, $value)
	{
		switch($type)
		{
			case 'INTEGER':
			case 'FLOAT':
				$value = (float) $value;
				$value = round($value, 2);
				$value = craft()->numberFormatter->formatDecimal($value);
				break;

			case 'CURRENCY':
				$currency = 'USD';
				$value = (float) $value;
				$value = round($value, 2);
				$value = craft()->numberFormatter->formatDecimal($value);
				$value = craft()->numberFormatter->formatCurrency($value, $currency);
				break;

			case 'TIME':
				$value = (float) $value;
				$value = $this->formatTime($value);
				break;

			case 'PERCENT':
				$value = (float) $value;
				$value = round($value, 2);
				$value = $value.'%';

				break;

			default:
				$value = (string) $value;
		}

		return (string) $value;
	}

	/**
	 * Format Time in HH:MM:SS from seconds
	 *
	 * @param int $seconds
	 */
	private function formatTime($seconds)
	{
		return gmdate("H:i:s", $seconds);
	}

	/**
	 * Transforms a GA Data object to an array
	 */
	private function parseGoogleAnalyticsResponse($data)
	{
		// Columns

		$cols = [];

		foreach($data->columnHeaders as $col)
		{
			// define the right type for the chart

			$dataType = $col->dataType;
			$type = $col->dataType;
			$id = $col->name;
			$label = craft()->analytics_metadata->getDimMet($col->name);

			switch($col->name)
			{
				case 'ga:date':
				case 'ga:yearMonth':
					$type = 'date';
					break;

				case 'ga:latitude':
				case 'ga:longitude':
					$type = 'number';
					$dataType = 'FLOAT';
					break;
			}

			switch($type)
			{
				case 'INTEGER':
				case 'CURRENCY':
				case 'FLOAT':
				case 'TIME':
				case 'PERCENT':
					$type = 'number';
					break;

				case 'STRING':
					$type = 'string';
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
				foreach($row as $kCell => $value)
				{
					$col = $cols[$kCell];

					// replace value by cell

					$cell = array(
						'v' => $this->formatRawValue($col['dataType'], $value),
						'f' => $this->formatValue($col['dataType'], $value)
					);

					if($col['id'] == 'ga:continent')
					{
						$cell['v'] = craft()->analytics_metadata->getContinentCode($cell['v']);
					}

					if($col['id'] == 'ga:subContinent')
					{
						$cell['v'] = craft()->analytics_metadata->getSubContinentCode($cell['v']);
					}

					// translate values
					switch($col['id'])
					{
						case 'ga:country':
						case 'ga:city':
						case 'ga:continent':
						case 'ga:subContinent':
						case 'ga:userType':
						case 'ga:javaEnabled':
						case 'ga:deviceCategory':
						case 'ga:mobileInputSelector':
						case 'ga:channelGrouping':
						case 'ga:medium':
							$cell['f'] = Craft::t($cell['f']);
							break;
					}

					// update cell
					$rows[$kRow][$kCell] = $cell;
				}
			}
		}

		return array(
			'cols' => $cols,
			'rows' => $rows
		);
	}

	/**
	 * Get Data Realtime
	 * @return \Google_Service_Analytics_DataRealtime_Resource
	 */
	public function getDataRealtime()
	{
		$api = $this->api();

		if($api)
		{
			return $api->data_realtime;
		}
	}

	/**
	 * Get Data GA
	 *
	 * @return \Google_Service_Analytics_DataGa_Resource
	 */
	public function getDataGa()
	{
		$api = $this->api();

		if($api)
		{
			return $api->data_ga;
		}
	}

	/**
	 * Get management web properties
	 *
	 * @return \Google_Service_Analytics_ManagementWebproperties_Resource
	 */
	public function getManagementWebproperties()
	{
		$api = $this->api();

		if($api)
		{
			return $api->management_webproperties;
		}
	}

	/**
	 * Get management profiles
	 *
	 * @return \Google_Service_Analytics_ManagementProfiles_Resource
	 */
	public function getManagementProfiles()
	{
		$api = $this->api();

		if($api)
		{
			return $api->management_profiles;
		}
	}

	/**
	 * Get metadata columns
	 *
	 * @return \Google_Service_Analytics_MetadataColumns_Resource
	 */
	public function getMetadataColumns()
	{
		$api = $this->api();

		if($api)
		{
			return $api->metadata_columns;
		}
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns a API object
	 *
	 * @return bool|Google_Service_Analytics
	 */
	private function api()
	{
		$handle = $this->oauthHandle;


		// provider

		$provider = craft()->oauth->getProvider($handle);

		if($provider)
		{
			// token
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

				// client
				$client = new Google_Client();
				$client->setApplicationName('Google+ PHP Starter Application');
				$client->setClientId('clientId');
				$client->setClientSecret('clientSecret');
				$client->setRedirectUri('redirectUri');
				$client->setAccessToken($arrayToken);

				$api = new Google_Service_Analytics($client);

				return $api;
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
